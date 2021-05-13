#-*- coding: utf-8 -*-
import os
from APP.utils import randstr



# with open('.secret_key', 'a+b') as secret:
#     secret.seek(0)
#     key = secret.read()
#     if not key:
#         key = randstr(32)
#         secret.write(key)
#         secret.flush()



class Config(object):
    SECRET_KEY = '\x9d\xe4\xfb\x0c\xaf\xe7\\\x9a\xd6q\xe2\xf1\xdf\xba\xa0`\x85\xd9P,\x07;\x98J'

    SQLALCHEMY_DATABASE_URI = 'sqlite:///{}/app.db'.format(os.path.dirname(os.path.abspath(__file__)))

    SQLALCHEMY_TRACK_MODIFICATIONS = True


    UPLOAD_FOLDER = os.path.join(os.path.dirname(__file__), 'static', 'uploads/')
 


class TestingConfig(Config):
    DEBUG = True


class DevelopingConfig(Config):
    DEBUG = False


class DBTestingConfig(TestingConfig):
    SQLALCHEMY_ECHO = True


config = {
    'dbtesting': DBTestingConfig,
    'testing': TestingConfig,
    'developing': DevelopingConfig
}