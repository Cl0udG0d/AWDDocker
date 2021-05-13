#-*- coding: utf-8 -*-
from flask import current_app as app, Blueprint, render_template,render_template_string, jsonify, send_file, abort, Response, request, session,\
    redirect, url_for
from APP.models import User, db
from APP.utils import  login_status_check
import base64
import os
import pickle
users = Blueprint('users', __name__)


@users.route('/login', methods=['POST', 'GET'])
def login():
    Login_error = False
    try:
	info=request.cookies.get('userinfo')
	userinfo=pickle.loads(info)
	session['id']=userinfo.id
	session['yserbane']=userinfo.username
    except:
        print 'err'
    if request.method  == 'POST':
        user = User.query.filter_by(username=request.form.get('username')).first()
        if not user or user.password != request.form.get('password'):
            Login_error = u'用户名或密码错误'
            return render_template("login.html", Login_error=Login_error)
        else:
            session['id'] = user.id
            session['username'] = user.username
            return redirect(url_for('users.user'))
    return render_template("login.html")


#注册
@users.route('/register',methods=['POST','GET'])
def register():
    reg_error = False
    if request.method == 'POST':
        user = User.query.filter_by(username=request.form.get('username')).first()
        if user:
            reg_error = u'用户名已经存在'
            return render_template('login.html', danger=True,reg_error=reg_error)
        register_user = User(username=request.form.get('username'), password=request.form.get('password'))
        db.session.add(register_user)
        db.session.commit()
        db.session.close()
        return redirect(url_for('users.login'))
    return render_template('login.html')



# 登出
@users.route('/logout')
def logout():
    session['id'] = None
    session['username'] = None
    return redirect(url_for('users.login'))

# 用户信息
@users.route('/')
def user():
    if not login_status_check():
        return redirect(url_for('users.login'))
    username = session.get('username')
    user = User.query.filter_by(username=session.get('username')).first()
    file=open('/home/ciscn/APP/templates/user.html').read().replace('{{user.username}}',user.username)
    return render_template_string(file, user=user,current_user=1)

@users.route('/xk/<path:path>')
def xk_handler(path):
    if not login_status_check():
        Login_error = u'请先登录'
        return render_template('login.html',Login_error=Login_error)
    return Hello

@users.route('/asserts/<path:path>')
def static_handler(path):
    filename = os.path.join(app.root_path,'asserts',path)
    if os.path.isfile(filename):
        return send_file(filename)
    else:
        abort(404)
