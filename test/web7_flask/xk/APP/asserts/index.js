$(function () {
	$('#login').click(login);
	$('#register').click(register);
	$('#Error').modal({keyboard: false});
});


window.login = function () {
	if($("#uid").val()=='')
	{
		// alert('用户名为空!');
		$('#emptyUserPassword').modal({keyboard: false});
		$('#emptyUserPassword .modal-body').text('用户名不能为空!');
		return;
	}
	else if($("#password").val()=='')
	{
		$('#emptyUserPassword').modal({keyboard: false});
		$('#emptyUserPassword .modal-body').text('密码不能为空!');
		return;
	}
	else {
        // $('#loading').removeClass('hide').addClass('show');         //显示等待框
		$("#Form").attr("action","/login");
		$("#Form").submit();
	}
};

window.register = function () {
	if($("#uid").val()=='')
	{
		// alert('用户名为空!');
		$('#emptyUserPassword').modal({keyboard: false});
		$('#emptyUserPassword .modal-body').text('用户名不能为空!');
		return;
	}
	else if($("#password").val()=='')
	{
		$('#emptyUserPassword').modal({keyboard: false});
		$('#emptyUserPassword .modal-body').text('密码不能为空!');
		return;
	}
	else {
        // $('#loading').removeClass('hide').addClass('show');         //显示等待框
		$("#Form").attr("action","/register");
		$("#Form").submit();
	}
};