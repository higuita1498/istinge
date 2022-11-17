<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Sistema de cotización Avanzado</title>
  <!-- plugins:css -->
   <link href="{{asset('vendors/iconfonts/mdi/css/materialdesignicons.min.css')}}" rel="stylesheet" type="text/css"/>
   <link href="{{asset('vendors/css/vendor.bundle.base.css')}}" rel="stylesheet" type="text/css"/>
   <link href="{{asset('vendors/css/vendor.bundle.addons.css')}}" rel="stylesheet" type="text/css"/> 
   <link href="{{asset('vendors/fontawesome/css/all.css')}}" rel="stylesheet" />
   <link href="https://fonts.googleapis.com/css?family=Noto+Serif" rel="stylesheet">
  <!-- endinject -->
  <!-- plugin css for this page -->
  <!-- End plugin css for this page -->
  <!-- inject:css -->
   <link href="{{asset('css/style.css')}}" rel="stylesheet" type="text/css"/>
  <!-- endinject -->
   <link href="{{asset('images/favicon.png')}}" rel="stylesheet" type="text/css"/><style type="text/css">
    * {
  margin: 0px; 
  padding: 0px; 
  box-sizing: border-box;
}

body, html {
  height: 100%;
  font-family: Montserrat, sans-serif;
}

/*---------------------------------------------*/
a {
  font-family: Montserrat;
  font-size: 14px;
  line-height: 1.7;
  color: #666666;
  margin: 0px;
  transition: all 0.4s;
  -webkit-transition: all 0.4s;
  -o-transition: all 0.4s;
  -moz-transition: all 0.4s;
}

a:focus {
  outline: none !important;
}

a:hover {
  text-decoration: none;
  color: #fff;
}

/*---------------------------------------------*/
h1,h2,h3,h4,h5,h6 {
  margin: 0px;
}

p {
  font-family: Montserrat;
  font-size: 14px;
  line-height: 1.7;
  color: #666666;
  margin: 0px;
}

ul, li {
  margin: 0px;
  list-style-type: none;
}


/*---------------------------------------------*/
input {
  outline: none;
  border: none;
}

textarea {
  outline: none;
  border: none;
}

textarea:focus, input:focus {
  border-color: transparent !important;
}

input:focus::-webkit-input-placeholder { color:transparent; }
input:focus:-moz-placeholder { color:transparent; }
input:focus::-moz-placeholder { color:transparent; }
input:focus:-ms-input-placeholder { color:transparent; }

textarea:focus::-webkit-input-placeholder { color:transparent; }
textarea:focus:-moz-placeholder { color:transparent; }
textarea:focus::-moz-placeholder { color:transparent; }
textarea:focus:-ms-input-placeholder { color:transparent; }

input::-webkit-input-placeholder { color: #999999; }
input:-moz-placeholder { color: #999999; }
input::-moz-placeholder { color: #999999; }
input:-ms-input-placeholder { color: #999999; }

textarea::-webkit-input-placeholder { color: #999999; }
textarea:-moz-placeholder { color: #999999; }
textarea::-moz-placeholder { color: #999999; }
textarea:-ms-input-placeholder { color: #999999; }

/*---------------------------------------------*/
button {
  outline: none !important;
  border: none;
  background: transparent;
}

button:hover {
  cursor: pointer;
}

iframe {
  border: none !important;
}


/*//////////////////////////////////////////////////////////////////
[ Utility ]*/
.m-b-10 {
    margin-bottom: 10px;
}
.p-b-30 {
    padding-bottom: 0;
}
.p-t-190 {
        margin-top: 6%;
}
.p-t-10 {
    padding-top: 10px;
}
.w-full {
    width: 100%;
}
.p-b-230 {
    padding-bottom: 230px;
}
.p-t-25 {
    padding-top: 25px;
}
.text-center {
    text-align: center!important;
}
.txt1 {
  font-family: Montserrat;
  font-size: 16px;
  color: #cccccc;
  line-height: 1.4;
}

.bo1 {
  border-bottom: 1px solid #999999;
}

.hov1:hover {
  border-color: #d33f8d;
}


/*//////////////////////////////////////////////////////////////////
[ login ]*/

.limiter {
  width: 100%;
  margin: 0 auto;
}

.container-login100 {
  width: 100%;  
  min-height: 100vh;
  display: -webkit-box;
  display: -webkit-flex;
  display: -moz-box;
  display: -ms-flexbox;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
  align-items: center;
  padding: 15px;
  background-repeat: no-repeat;
  background-size: cover;
  background-position: center;
  
  position: relative;
  z-index: 1;
}

.container-login100::before {
  content: "";
  display: block;
  position: absolute;
  z-index: -1;
  width: 100%;
  height: 100%;
  top: 0;
  left: 0;
  opacity: 0.7;
}

.wrap-login100 {
  width: 390px;
  background: transparent;
}



/*------------------------------------------------------------------
[  ]*/
.login100-form {
  width: 100%;
  display: -webkit-box;
  display: -webkit-flex;
  display: -moz-box;
  display: -ms-flexbox;
  display: flex;
  justify-content: space-between;
  flex-wrap: wrap;
}

.login100-form-title {
  font-family: Montserrat;
  font-size: 24px;
  color: #fff;
  line-height: 1.2;
  text-align: center;

  width: 100%;
  display: block;
}

/*---------------------------------------------*/
.login100-form-avatar {
  width: 250px;
  height: 230px;
  overflow: hidden;
  margin: 0 auto;
}
.login100-form-avatar img {
  width: 100%;
}


/*---------------------------------------------*/
.wrap-input100 {
  position: relative;
  width: 100%;
  z-index: 1;
}

.input100 {
  font-family: Montserrat;
  font-size: 15px;
  line-height: 1.2;
  color: #333333;

  display: block;
  width: 100%;
  background: #fff;
  height: 50px;
  border-radius: 25px;
  padding: 0 30px 0 53px;
}


/*------------------------------------------------------------------
[ Focus ]*/
.focus-input100 {
  display: block;
  position: absolute;
  border-radius: 25px;
  bottom: 0;
  left: 0;
  z-index: -1;
  width: 100%;
  height: 100%;
  box-shadow: 0px 0px 0px 0px;
  color: #eb8011;
}

.input100:focus + .focus-input100 {
  -webkit-animation: anim-shadow 0.5s ease-in-out forwards;
  animation: anim-shadow 0.5s ease-in-out forwards;
}

@-webkit-keyframes anim-shadow {
  to {
    box-shadow: 0px 0px 80px 30px;
    opacity: 0;
  }
}

@keyframes anim-shadow {
  to {
    box-shadow: 0px 0px 80px 30px;
    opacity: 0;
  }
}

.symbol-input100 {
  font-size: 15px;
  color: #999999;

  display: -webkit-box;
  display: -webkit-flex;
  display: -moz-box;
  display: -ms-flexbox;
  display: flex;
  align-items: center;
  position: absolute;
  border-radius: 25px;
  bottom: 0;
  left: 0;
  width: 100%;
  height: 100%;
  padding-left: 30px;
  pointer-events: none;

  -webkit-transition: all 0.4s;
  -o-transition: all 0.4s;
  -moz-transition: all 0.4s;
  transition: all 0.4s;
}

.input100:focus + .focus-input100 + .symbol-input100 {
  color: #eb8011;
  padding-left: 23px;
}


/*------------------------------------------------------------------
[ Button ]*/
.container-login100-form-btn {
  width: 100%;
  display: -webkit-box;
  display: -webkit-flex;
  display: -moz-box;
  display: -ms-flexbox;
  display: flex;
  flex-wrap: wrap;
  justify-content: center;
}

.login100-form-btn {
  font-family: Montserrat;
  font-size: 15px;
  line-height: 1.5;
  color: #e0e0e0;

  width: 100%;
  height: 50px;
  border-radius: 25px;
  background: #333333;
  display: -webkit-box;
  display: -webkit-flex;
  display: -moz-box;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
  align-items: center;
  padding: 0 25px;

  -webkit-transition: all 0.4s;
  -o-transition: all 0.4s;
  -moz-transition: all 0.4s;
  transition: all 0.4s;

  position: relative;
  z-index: 1;
}

.login100-form-btn::before {
  content: "";
  display: block;
  position: absolute;
  z-index: -1;
  width: 100%;
  height: 100%;
  border-radius: 25px;
  top: 0;
  left: 0;
  background: #ff7e00;
  background: -webkit-linear-gradient(left, #ff7e00, #ad864b);
  background: -o-linear-gradient(left, #e43228, #00c6fb);
  background: -moz-linear-gradient(left, #e43228, #00c6fb);
  background: linear-gradient(left, #e43228, #00c6fb);
  -webkit-transition: all 0.4s;
  -o-transition: all 0.4s;
  -moz-transition: all 0.4s;
  transition: all 0.4s;
  opacity: 0;
}

.login100-form-btn:hover {
  background: transparent;
  color: #fff;
}

.login100-form-btn:hover:before {
  opacity: 1;
}

/*------------------------------------------------------------------
[ Button sign in with ]*/
.btn-face,
.btn-google {
  font-family: Raleway;
  font-size: 16px;
  line-height: 1.2;

  display: -webkit-box;
  display: -webkit-flex;
  display: -moz-box;
  display: -ms-flexbox;
  display: flex;
  justify-content: center;
  align-items: center;
  width: calc((100% - 10px) / 2);
  height: 40px;
  border-radius: 3px;
  border: 1px solid #e6e6e6;
  background-color: #fff;

  -webkit-transition: all 0.4s;
  -o-transition: all 0.4s;
  -moz-transition: all 0.4s;
  transition: all 0.4s;
}


.btn-face {
  color: #3b5998;
}

.btn-face i {
  font-size: 20px;
  margin-right: 10px;
  padding-bottom: 1px;
}

.btn-google {
  color: #555555;
}

.btn-google img {
  width: 19px;
  margin-right: 10px;
  padding-bottom: 1px;
}


.btn-face:hover,
.btn-google:hover {
  border-color: #d33f8d;
}



/*------------------------------------------------------------------
[ Alert validate ]*/

.validate-input {
  position: relative;
}

.alert-validate::before {
  content: attr(data-validate);
  position: absolute;
  max-width: 70%;
  background-color: white;
  border: 1px solid #c80000;
  border-radius: 14px;
  padding: 4px 25px 4px 10px;
  top: 50%;
  -webkit-transform: translateY(-50%);
  -moz-transform: translateY(-50%);
  -ms-transform: translateY(-50%);
  -o-transform: translateY(-50%);
  transform: translateY(-50%);
  right: 8px;
  pointer-events: none;

  font-family: Montserrat;
  color: #c80000;
  font-size: 13px;
  line-height: 1.4;
  text-align: left;

  visibility: hidden;
  opacity: 0;

  -webkit-transition: opacity 0.4s;
  -o-transition: opacity 0.4s;
  -moz-transition: opacity 0.4s;
  transition: opacity 0.4s;
}

.alert-validate::after {
  content: "\f06a";
  font-family: FontAwesome;
  display: block;
  position: absolute;
  color: #c80000;
  font-size: 15px;
  top: 50%;
  -webkit-transform: translateY(-50%);
  -moz-transform: translateY(-50%);
  -ms-transform: translateY(-50%);
  -o-transform: translateY(-50%);
  transform: translateY(-50%);
  right: 13px;
}

.alert-validate:hover:before {
  visibility: visible;
  opacity: 1;
}

@media (max-width: 992px) {
  .alert-validate::before {
    visibility: visible;
    opacity: 1;
  }
}

/*//////////////////////////////////////////////////////////////////
[ responsive ]*/

@media (max-width: 576px) {
  .wrap-login100 {
    padding-top: 80px;
    padding-left: 15px;
    padding-right: 15px;
  }
}


label{
      margin-left: 8%;
    color: #fff;
  
}

    #video_background {
        position: absolute;
        bottom: 0px;
        right: 0px;
        min-width: 100%;
        min-height: 100%;
        width: auto;
        height: auto;
        z-index: -1000;
        overflow: hidden;
    }
    #video_pattern {
        background:#fff;
        position: fixed;
        opacity: 0.8;
        left: 0px;
        top: 0px;
        width: 100%;
        height: 100%;
        z-index: 1;
    }

</style>
</head>

<body>
  <div class="limiter">
    <div class="container-login100" style="background-image: url('{{asset('images/auth/login_1.jpg')}}');">
      <div class="wrap-login100 p-t-190 p-b-30">
        @yield('content')
      </div>
    </div>
  </div>
  <!--
  <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper auth-page">
          <div class="content-wrapper-auth d-flex align-items-center auth auth-bg-1 theme-one">

      @yield('content')

      </div>
       </div>
  </div>-->
  
  <!-- container-scroller -->
  <!-- plugins:js -->
  <script src="{{asset('vendors/js/vendor.bundle.base.js')}}"></script>
  <script src="{{asset('vendors/js/vendor.bundle.addons.js')}}"></script>
  <!-- endinject -->
  <!-- inject:js -->
  <script src="{{asset('js/off-canvas.js')}}"></script>
  <script src="{{asset('js/misc.js')}}"></script>
  <!-- endinject -->
  <script type="text/javascript">
          /*==================================================================
    [ Validate ]*/
    var input = $('.validate-input .input100');

    $('.validate-form').on('submit',function(){
        var check = true;

        for(var i=0; i<input.length; i++) {
            if(validate(input[i]) == false){
                showValidate(input[i]);
                check=false;
            }
        }

        return check;
    });


    $('.validate-form .input100').each(function(){
        $(this).focus(function(){
           hideValidate(this);
        });
    });

    function validate (input) {
        if($(input).attr('type') == 'email' || $(input).attr('name') == 'email') {
            if($(input).val().trim().match(/^([a-zA-Z0-9_\-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([a-zA-Z0-9\-]+\.)+))([a-zA-Z]{1,5}|[0-9]{1,3})(\]?)$/) == null) {
                return false;
            }
        }
        else {
            if($(input).val().trim() == ''){
                return false;
            }
        }
    }

    function showValidate(input) {
        var thisAlert = $(input).parent();

        $(thisAlert).addClass('alert-validate');
    }

    function hideValidate(input) {
        var thisAlert = $(input).parent();

        $(thisAlert).removeClass('alert-validate');
    }
    
    </script>
</body>

</html>
