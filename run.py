from flask import Flask, request, jsonify
import os
import urllib.parse
import fnmatch
import hashlib
import logging
from threading import Lock
import requests
from pathvalidate import sanitize_filename

# Configurar logs
logging.basicConfig(
    level=logging.INFO,
    format="[%(asctime)s][%(funcName)20s()][%(levelname)-8s]: %(message)s",
    handlers=[logging.StreamHandler()],
)
logger = logging.getLogger("GoFile")

app = Flask(__name__)

class File:
    def __init__(self, link: str, dest: str):
        self.link = link
        self.dest = dest

    def __str__(self):
        return f"{self.dest} ({self.link})"


class Downloader:
    def __init__(self, token):
        self.token = token

    def get_download_link(self, file: File):
        return file.link


class GoFileMeta(type):
    _instances = {}

    def __call__(cls, *args, **kwargs):
        if cls not in cls._instances:
            instance = super().__call__(*args, **kwargs)
            cls._instances[cls] = instance
        return cls._instances[cls]


class GoFile(metaclass=GoFileMeta):
    def __init__(self) -> None:
        self.token = ""
        self.wt = ""
        self.lock = Lock()

    def update_token(self) -> None:
        if self.token == "":
            data = requests.post("https://api.gofile.io/accounts").json()
            if data["status"] == "ok":
                self.token = data["data"]["token"]
                logger.info(f"updated token: {self.token}")
            else:
                raise Exception("cannot get token")

    def update_wt(self) -> None:
        if self.wt == "":
            alljs = requests.get("https://gofile.io/dist/js/global.js").text
            if 'appdata.wt = "' in alljs:
                self.wt = alljs.split('appdata.wt = "')[1].split('"')[0]
                logger.info(f"updated wt: {self.wt}")
            else:
                raise Exception("cannot get wt")

    def get_files(self, dir: str, content_id: str = None, url: str = None, password: str = None, excludes: list[str] = None) -> list:
        if excludes is None:
            excludes = []
        files = []
        if content_id is not None:
            self.update_token()
            self.update_wt()
            hash_password = hashlib.sha256(password.encode()).hexdigest() if password else ""
            data = requests.get(
                f"https://api.gofile.io/contents/{content_id}?wt={self.wt}&cache=true&password={hash_password}",
                headers={"Authorization": "Bearer " + self.token},
            ).json()
            if data["status"] == "ok":
                if data["data"].get("passwordStatus", "passwordOk") == "passwordOk":
                    if data["data"]["type"] == "folder":
                        dirname = data["data"]["name"]
                        dir = os.path.join(dir, sanitize_filename(dirname))
                        for (id, child) in data["data"]["children"].items():
                            if child["type"] == "folder":
                                self.get_files(dir=dir, content_id=id, password=password)
                            else:
                                filename = child["name"]
                                if not any(fnmatch.fnmatch(filename, pattern) for pattern in excludes):
                                    files.append(File(
                                        link=urllib.parse.unquote(child["link"]),
                                        dest=urllib.parse.unquote(os.path.join(dir, sanitize_filename(filename)))
                                    ))
                    else:
                        filename = data["data"]["name"]
                        if not any(fnmatch.fnmatch(filename, pattern) for pattern in excludes):
                            files.append(File(
                                link=urllib.parse.unquote(data["data"]["link"]),
                                dest=urllib.parse.unquote(os.path.join(dir, sanitize_filename(filename)))
                            ))
                else:
                    logger.error(f"invalid password: {data['data'].get('passwordStatus')}")
        elif url is not None:
            if url.startswith("https://gofile.io/d/"):
                files = self.get_files(dir=dir, content_id=url.split("/")[-1], password=password, excludes=excludes)
            else:
                logger.error(f"invalid url: {url}")
        else:
            logger.error(f"invalid parameters")
        return files


@app.route("/", methods=["GET"])
def api():
    url = request.args.get("url")
    password = request.args.get("password")
    excludes = request.args.getlist("exclude")

    if not url:
        return jsonify({"error": "Falta el parámetro 'url'"}), 400

    output_dir = "./output"
    os.makedirs(output_dir, exist_ok=True)

    try:
        gofile = GoFile()
        files = gofile.get_files(dir=output_dir, url=url, password=password, excludes=excludes)
        results = [{"file": f.dest, "link": f.link} for f in files]
        return jsonify({"status": "ok", "links": results})
    except Exception as e:
        return jsonify({"status": "error", "message": str(e)}), 500


if __name__ == "__main__":
    app.run(host="0.0.0.0", port=10000)
