$(function(){
	//注册
	$('#regist').click(function(){
		//客户端校验
		registerCheck();
		//服务器端验证
		//...
	})

	//登陆
	/*$("#login").click(function(){
		//客户端校验
		loginCheck();
		window.location.href="{:U('Norepeatstr/index/regist')}";
		//服务器端验证
		//...
	})*/

	//忘记密码
	$("#pwdNext").click(function(){
		if(forgetPwdCheck()){
			$(".forgetPwd-form1").hide();
			$(".forgetPwd-form2").show();
		}
		
		//服务器端验证手机号是否存在
	})
})
//显示错误信息
function showError(str,ele){
	if(!ele){
		ele = $("#error");
	}
	ele.text(str).show();
	/*setTimeout(function(){
		ele.hide();
	},2500);*/
}
//注册校验
function registerCheck() {
	 if($("#regist_inputpwd2").val() != $("#regist_inputpwd").val()){
		$("#error").css("top","400px");
		showError('密码不一致');
		$("#regist_inputpwd2").focus();
		return false;
	}else{
		$("#error").hide();
		return true;
	}/*else if(!$('.cRule').prop("checked")){
		showError('请接受用户协议~');
	}*/
}
function loginCheck(){
	if($("#inputnum").val() == "") {
		$("#error").css("top","-80px");
		showError('不能为空');
		$("#inputnum").focus();
		return false;
	}else if(!/^1[3|4|5|8]\d{9}$/.test($("#inputnum").val().trim())){
		$("#error").css("top","-80px");
		showError('手机号不正确');
		$("#inputnum").focus();
		return false;
	}else if ($("#inputpwd").val() == "") {
		$("#error").css("top","138px");
		showError('密码不得为空');
		$("#inputpwd").focus();
		return false;
	}else{
		$("#error").hide();
		return true;
	}
}
function forgetPwdCheck(){
	if($(".phoneNum").val() == "") {
		showError('手机号不得为空');
		$(".phoneNum").focus();
		return false;
	}else if(!/^1[3|4|5|8]\d{9}$/.test($(".phoneNum").val().trim())){
		showError('请输入正确的手机号');
		$(".phoneNum").focus();
		return false;
	}else if ($(".pwd").val() == "") {
		showError('密码不得为空');
		$(".pwd").focus();
		return false;
	}else if(!/\w{6,20}/.test($(".pwd").val())){
		showError('请输入6-20位字符');
		$(".pwd").focus();
		return false;
	}else if ($(".pwd2").val() == "") {
		showError('请再次输入密码');
		$(".pwd2").focus();
		return false;
	}else if($(".pwd2").val() !== $(".pwd").val()){
		showError('两次输入的密码不一致');
		$(".pwd2").focus();
		return false;
	}else{
		return true;
	}
 
}