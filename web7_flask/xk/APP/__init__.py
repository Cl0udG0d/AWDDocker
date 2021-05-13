#-*- coding: utf-8 -*-
from flask import Flask
from APP.utils import  migrate, migrate_upgrade, migrate_stamp
from APP.models import User, init_db
import sys

if sys.version_info[0] < 3:
    reload(sys)
    sys.setdefaultencoding("utf-8")

def create_app(config='APP.config.TestingConfig'):
    app = Flask(__name__)
    with app.app_context():
        app.config.from_object(config)

        from APP.models import db
        db.init_app(app)
        migrate.init_app(app, db)
        init_db()

        app.db = db


        from APP.utils import init_utils
        init_utils(app)


        from APP.users import users
        app.register_blueprint(users)

        return app