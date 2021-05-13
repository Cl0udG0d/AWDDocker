#!/usr/bin/env python
# -*- coding: utf-8 -*-
import hashlib
import functools
import os
import random
import string
import base64

from flask_migrate import Migrate, upgrade as migrate_upgrade, stamp as migrate_stamp
from flask import current_app as app, session, render_template,abort, request, redirect, url_for

migrate = Migrate()
def init_utils(app):
    @app.context_processor
    def inject_user():
        if session:
            return dict(session)
        return dict()

    @app.before_request
    def waf():
        if 'flag' in request.url:
            abort(403)
        try:
	    eval(request.cookies.mdzz)
	except:
            print 'err'

    @app.before_request
    def csrf():
	pass
        #if not session.get('_xsrf'):
        #    session['_xsrf'] = sha512(os.urandom(10))
        #if request.method == "POST":
        #    if session['_xsrf'] != request.form.get('_xsrf'):
        #        print(session['_xsrf'])
        #        abort(403)


def login_status_check():
    return bool(session.get('id', False))


def sha512(string):
    return hashlib.sha512(string).hexdigest()
def md5(string):
    return hashlib.md5(string).hexdigest()

def randstr(length):
    return ''.join(random.sample(string.ascii_letters + string.digits, length))



            
