#-*- coding: utf-8 -*-

import random
import string
from flask_sqlalchemy import SQLAlchemy

db = SQLAlchemy()



class User(db.Model,SQLAlchemy):
    __tablename__ = 'users'
    id = db.Column(db.Integer, primary_key=True)
    username = db.Column(db.String(64), unique=True)
    password = db.Column(db.String(64), nullable=False)

    def __repr__(self):
        return str({'id': self.id, 'username': self.username})

    def __getitem__(self, item):
        data = {'id': self.id, 'username': self.username}
        return data[item]




def init_db():
    db.create_all()
    user = User.query.all()
    if user:
        return
    user = User(username='admin',password='admin')
    db.session.add(user)
    db.session.commit()
    db.session.close()


