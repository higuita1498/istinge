@extends('layouts.app')

@section('style')
<style type="text/css">
    .body-card>.row{
        display:none;
    }
    .card{
        margin-bottom:23px !important;
    }
    .stretch-card{
        margin-bottom:0px !important;
    }
    .content-wrapper{
        padding-bottom:0px !important;
    }
    .footer{
        display:none !important;
    }
    html{
        overflow-y: auto;
        max-height:100vh;
    }
    #reloadButton{
        border: none;box-shadow: 
        0px 0px 6px 5px rgba(0, 0, 0, 0.2);
    }
    #reloadButton:hover{
        border: none;box-shadow: 
        0px 0px 6px 5px rgba(0, 0, 0, 0.0);
    }
    .users-container {
        border-right: 1px solid #e4e4e4;
    }

    .users {
        padding: 0;
        height: calc(100% - 70px);
        overflow-y: auto;
        
    }

    .users .person {
        position: relative;
        width: 100%;
        padding: 0px 0.7rem;
        cursor: pointer;
    }

    .users .person:hover {
        background-color: #ebebeb;
    }

    .users .person.active-user {
        background-color: #daeaff;
    }

    .users .person:last-child {
        border-bottom: 0;
    }

    .users .person .user {
        margin-right: 10px;
        float: left;
        position: relative;
        left: 0px;
        top: 10px;
    }

    .users .person p.name-time .name{
        display: block;
        margin-bottom: -4px;
        margin-top: 4px;
    }

    .users .person .user img {
        width: 40px;
        height: 40px;
        -webkit-border-radius: 50px;
        -moz-border-radius: 50px;
        border-radius: 50px;
        border: 1px solid #eee;
    }

    .users .person .user .status {
        width: 10px;
        height: 10px;
        -webkit-border-radius: 100px;
        -moz-border-radius: 100px;
        border-radius: 100px;
        background: #e6ecf3;
        position: absolute;
        top: 0;
        right: 0;
    }

    .users .person .user .status.online {
        background: #9ec94a;
    }

    .users .person .user .status.offline {
        background: #c4d2e2;
    }

    .users .person .user .status.away {
        background: #f9be52;
    }

    .users .person .user .status.busy {
        background: #fd7274;
    }

    .users .person p.name-time {
        font-weight: 600;
        font-size: .8rem;
        display: inline-block;
        width: calc(100% - 55px);
        margin-bottom: 12px;
        border-top: 1px solid #e9edef;
    }

    .users .person p.name-time .time {
        font-weight: 600;
        font-size: .7rem;
        text-align: right;
        color: #8796af;
        float: right;
    }

    .conter-chat-user{
        text-align: center;
        background-color: #506de3;
        border-radius: 1.1em;
        color: #fff;
        min-width: 23px;
        line-height: 23px;
        margin-top: 0px;
        margin-bottom: 0;
        padding: 0;
        position: absolute;
        font-weight: 600;
        bottom: 40px;
        right: 17px;
    }

    .conter-chat-user:empty{
        display: none;
    }


    .documentchat{
        display: flex;
        background: #cfe9ba;
        padding: 10px;
        border-radius: 5px 5px 0 0;
        color: #4a4a4a;
    }

    .check-w-0,.check-w-1{
        font-family: "Font Awesome 5 Free";
        -webkit-font-smoothing: antialiased;
        display: inline-block;
        font-style: normal;
        font-variant: normal;
        text-rendering: auto;
        line-height: 1;
        font-size: 11px !important;
        margin-left: 6px;
        color: #3e3e3e !important;
        font-weight: 900;
    }
    .check-w-0:before,.check-w-1:before{
        content: "\f00c";
    }

    .check-w-3{
        font-family: "Font Awesome 5 Free";
        -webkit-font-smoothing: antialiased;
        display: inline-block;
        font-style: normal;
        font-variant: normal;
        text-rendering: auto;
        line-height: 1;
        color: #57b7f1 !important;
        font-weight: 600;
        font-size: 11px !important;
        margin-left: 6px;
    }
    .check-w-3:before{
        content: "\f560";
    }

    .check-w-2{
        font-family: "Font Awesome 6 Free";
        -webkit-font-smoothing: antialiased;
        display: inline-block;
        font-style: normal;
        font-variant: normal;
        text-rendering: auto;
        line-height: 1;
        color: #bdbdbd !important;
        font-weight: 600;
        font-size: 11px !important;
        margin-left: 6px;
    }
    .check-w-2:before{
        content: "\f560";
    }

    .icon_chat_file{
        width: 30px;
        height: 32px;
        display: inline-block;
        float: left;
        background-size: 25px 30px;
        background-repeat: no-repeat;
        background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADMAAAA8CAMAAAD8KXLNAAAAulBMVEUAAACPw/ePw/lgqvVfqvaUx/mQxPhfqfWjz/lfqfVfqvZgp/9cp/ZfqvZgqvaayPpTpPVfqvVjpvNgqfaQw/mjzvq42PdfqvZgqvZiq/ZwsveRxflkqe9rsPdcqPZeqPRorvfN5fzw9/5bp/Z2tvdZoOqTxvqCuvPj8P7+//+QxPmBvfl3su+Pw/lWpfWx1fuJwPlUpPWVxvnp8/7////4+/9ure3C3/uq0fvb7P1usffn8v15tfFMmOceGILeAAAAF3RSTlMAIdHm7ijJ+JH8eQeTzaw68foX1Ll9kGZdllAAAAEQSURBVHgB7dZVcsMwFIVhhTkp5krmMJcZ9r+sMl7PEczopfC/f7ZFtoUQreZuWVepJ3g73VKtoSvaKzDS6kp9abSsMtQumQ1HtYbZcCSl2TBkZRiyNU+o4m6WxYqzeUIFd/P0eO7m6fGsDUNmQ+lsypA0ll6E4XdkYSbrTTj96AlJczSJ1rOP7vrQsFt9dl/HBnX8y00Cg0YFqBtkKJuDbgdEwMSLM9Cp9GnUCWoD54AOYH7XZwRDhtQRagVNfA5azKXX9RmirpGRaowKfM6bt/NDekPZYa6rIAEGz/VwNfJvskGuy3FimAOVzzhvlA+Z//f1vyltu5p90Sw5mq2eEG1H03759e+Ua7aVO82WeASxdDP0M8Z9fAAAAABJRU5ErkJggg==);
    }

    .name_file_w{
        max-width: 220px;
        display: inline-block;
        float: left;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        line-height: 32px;
        margin-right: 5px;
    }

    .icon_down_w{
        width: 32px;
        height: 32px;
        display: inline-block;
        float: left;
        color: #9a9a9a;
    }

    .headerchat{
        background: #fff;
        height: 50px;
        position: inherit;
        border-bottom: 1px solid #ddd;
    }
    .chatContainerScroll{
        height: calc(100% - 164px);
        margin-top: 5px;
        position: inherit;
        overflow: auto;
        padding: 0px 30px;
    }





    /************************************************
        ************************************************
                                        Chat right side
        ************************************************
    ************************************************/
    .chat-back{
        display: none;
    }
    .selected-user {
        width: 100%;
        padding: 0 15px;
        min-height: 64px;
        line-height: 64px;
        -webkit-border-radius: 0 3px 0 0;
        -moz-border-radius: 0 3px 0 0;
        border-radius: 0 3px 0 0;
        background: #f9f9f9;
        position: relative;
        border-bottom: 1px solid #e4e4e4;
    }

    .selected-user img{
            width: 48px;
        height: 48px;
        -webkit-border-radius: 24px;
        -moz-border-radius: 24px;
        border-radius: 24px;
    }

    .selected-user span {
        line-height: 100%;
    }

    .selected-user span.name {
        font-weight: 700;
    }

    .chat-container {
        position: relative;
        background-color: #ecf3fa;
    }

    .chat-container li.chat-left,
    .chat-container li.chat-right {
        display: flex;
        flex: 1;
        flex-direction: row;
        margin-bottom: 20px;
    }

    .chat-container li img {
        width: 48px;
        height: 48px;
        -webkit-border-radius: 24px;
        -moz-border-radius: 24px;
        border-radius: 24px;
    }

    .chat-container li .chat-avatar {
        margin-right: 20px;
    }

    .chat-container li.chat-right {
        justify-content: flex-end;
    }

    .chat-container li.chat-right > .chat-avatar {
        margin-left: 20px;
        margin-right: 0;
    }

    .chat-container li .chat-name {
        font-size: .75rem;
        color: #999999;
        text-align: center;
        max-width: 48px;
        white-space: nowrap;
    }

    .chat-container li .chat-text {
        padding: .4rem 1rem;
        -webkit-border-radius: 12px;
        -moz-border-radius: 12px;
        border-radius: 7px;
        background: #ffffff;
        font-weight: 500;
        line-height: 150%;
        position: relative;
        max-width: 65%;
        min-width: 150px;
        font-size: 13px;
        margin: 5px 20px;
        box-shadow: 0 1px 0.5px rgb(11 20 26 / 13%);
        border-bottom-left-radius: 0px;
    }


    .chat-container li .chat-text:before {
        content: '';
        position: absolute;
        width: 0;
        height: 0;
        bottom: 0px;
        left: -8px;
        transform: rotate(90deg);
        border: 10px solid;
        border-color: #ffffff #ffffff transparent transparent;
    }

    .chat-container li.chat-right > .chat-text {
        text-align:left;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 0px;
        background: #eeffde;
        box-shadow: 0 1px 0.5px #ddd;
    }
    #sendNewMessage::-webkit-scrollbar {
        width: 0.0em; /* Ancho de la barra de desplazamiento */
        background-color: #F5F5F5; /* Color de fondo de la barra de desplazamiento */
    }

    #sendNewMessage::-webkit-scrollbar-thumb {
        background-color: #888; /* Color del "pulgar" de la barra de desplazamiento */
    }

    #sendNewMessage::-webkit-scrollbar-thumb:hover {
        background-color: #555; /* Color del "pulgar" de la barra de desplazamiento al pasar el cursor */
    }
    .chat-container li.chat-right > .chat-text:before {
        right: -8px;
        transform: rotate(180deg);
        border-color: #eeffde #eeffde transparent transparent;
        left: inherit;
    }

    .chat-container .chat-hour {
        font-size: .70rem;
        color: #b1b1b1;
        text-align: right;
        width: 100%;
    }

    .headercharhour {
        color: #fff;
        background-color: hsl(211deg 87% 59% / 40%);
        font-size: .7375rem;
        padding: 0.28125rem 0.625rem;
        line-height: 1.25rem;
        user-select: none;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        word-break: break-word;
        width: fit-content;
        font-weight: 600;
        border-radius: 10px;
        margin: 0 auto;
    }

    .chat-container li.chat-right > .chat-hour {
        margin: 0 15px 0 0;
    }

    .barrasend{
        bottom: 0%;
        width: 100%;
        z-index: 4;
        height: 95px;
        line-height: 57px;
        position: absolute;
        border-radius: 0px !important;
        border-top: 1px solid #ddd;
    }


    @media (max-width: 767px) {


    }

    .chat-form {
        padding: 15px;
        width: 100%;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: #ffffff;
        border-top: 1px solid white;
    }

    ul {
        list-style-type: none;
        margin: 0;
        padding: 0;
    }
    .card {
        border: 0;
        background: #f4f5fb;
        -webkit-border-radius: 2px;
        -moz-border-radius: 2px;
        border-radius: 2px;
        margin-bottom: 2rem;
        box-shadow: none;
    }

    .imgchatwhap{
        width: auto !important;
        border-radius: 0px !important;
        height: 100% !important;
        max-height: 200px !important;
        max-width: 100%;
    }

    .chat-rely{
        padding: 5px;
        background: #f5f5f5;
        width: 100%;
        margin-bottom: 10px;
        border-radius: 5px;
        border-left: 4px solid #ad18de;
        font-size: 11px;
    }
    .chat-rely img{
        max-height: 60px !important;
        
    }

    .chat-rely audio{
        width: 121px;
        height: 30px;
    }
    .chat-left .documentchat{
        background: #efefef;
    }

    .imgmapa img{
        width: 250px !important;
        height: auto !important;
        border-radius: 0px !important;
    }

    .textmap{
        width: 250px;
        text-align: left;
    }

    .chat-rely .imgmapa img{
        max-height: 100px !important;
        width: auto !important;
    }
    .waadjunto {
        width: 100px;
        position: relative;
        height: 70px;
        z-index: 4;
    }
    .lastmessage{
        display: block;
        max-height: 17px;
        min-height: 17px;
        color: #a2a2a2;
        font-weight: 400;
        font-size: 12px;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
        max-width: calc(100% - 30px);
    }

    .users .tooltip {
        max-width:90% !important;
    }

    .users .tooltip-inner{
        max-width: 100%;
    }
    .messageasigned{
        position: absolute;
        width: 100%;
        background: #506de3;
        color: #fff;
        padding: 5px;
        font-size: 13px;
        text-align: center;
        z-index: 1;
    }

    .operadorasignado{
        color: #506de3;
        font-size: 10px;
        font-weight: 500;
    }
    .users li[data-estado="1"]{
        opacity: .25;
    }

    .closechatw{
        font-size: 15px;
        height: 35px;
        width: 35px;
        border: 1px solid #506de3;
        padding: 7px;
        border-radius: 50%;
        color: #506de3;
        font-weight: bold;
        cursor: pointer;
    }
    .hidenchats{
        display: none;
    }

    #sendNewMessage{
        display: block;
        width: 100%;
        font-size: .75rem;
        font-weight: 400;
        line-height: 1.5;
        color: #2d353c;
        transition: border-color .15s ease-in-out,box-shadow .15s ease-in-out;
        vertical-align: middle;
        padding: 5px 0;
        height: 60px;
        margin-top: 10px;
        background: #f2f4f5;
        border-radius: 40px;
        font-family: 'Poppins', sans-serif;
        padding-left: 18px;
    }
    .widget-input-container {
        display: block;
        width: 100%;
    }

    .widget-input-container .widget-input-icon,.widget-input-container .widget-input-box {
        display: block;
        float: left;
    }
    .widget-input-container .widget-input-box {
        width: calc(100% - 188px);
        margin-left: 20px;
    }

    .widget-input-container .widget-input-box .form-control {
        height: 60px;
        margin-top: 10px;
    }

    .widget-input-container .widget-input-icon a {
        padding: 18px;
    }


    .iconadjuntowa{
        display: inline-block;
        position: absolute;
        font-size: 30px;
        color: #696969 !important;
        width: 100px;
        padding-top: 0px;
        text-align: center;
        z-index: 1;
    }

    .parpadea {
    
    animation-name: parpadeo;
    animation-duration: 3s;
    animation-timing-function: linear;
    animation-iteration-count: infinite;

    -webkit-animation-name:parpadeo;
    -webkit-animation-duration: 3s;
    -webkit-animation-timing-function: linear;
    -webkit-animation-iteration-count: infinite;
    }

    @-moz-keyframes parpadeo{  
    0% { opacity: 1.0; }
    50% { opacity: 0.0; }
    100% { opacity: 1.0; }
    }

    @-webkit-keyframes parpadeo {  
    0% { opacity: 1.0; }
    50% { opacity: 0.0; }
    100% { opacity: 1.0; }
    }

    @keyframes parpadeo {  
    0% { opacity: 1.0; }
    50% { opacity: 0.0; }
    100% { opacity: 1.0; }
    }

    @-webkit-keyframes wave {
        0%,
        100%,
        60% {
            -webkit-transform: initial;
            transform: initial
        }
        30% {
            -webkit-transform: translateY(-5px);
            transform: translateY(-5px)
        }
    }

    @keyframes wave {
        0%,
        100%,
        60% {
            -webkit-transform: initial;
            transform: initial
        }
        30% {
            -webkit-transform: translateY(-5px);
            transform: translateY(-5px)
        }
    }

    #containertgs{
        position: absolute;
        z-index: 999999;
        bottom: 28px;
        width: 100%;
    }

    .ui-autocomplete {
        max-height: 210px;
        overflow-y: auto;
        overflow-x: hidden;
    }
    .ui-menu-item-wrapper div{
        font-size: 11px;
    }

    #showtyping{
        width: 100%;
        height: 35px;
        line-height: 35px;
        color: #506ce2;
        text-align: center;
        font-size: 14px;
        position: absolute;
        font-weight: normal;
        z-index: 1;
        margin-top: -28px;
    }

    .animate-typing .dot:nth-child(2) {
        -webkit-animation-delay: -1.1s;
        animation-delay: -1.1s;
    }

    .animate-typing .dot:nth-child(3) {
        -webkit-animation-delay: -.9s;
        animation-delay: -.9s;
    }

    .animate-typing{
        color: #0830de;
        font-weight: 500;
        font-size: 14px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .animate-typing .dot {
        display: inline-block;
        width: 4px;
        height: 4px;
        border-radius: 50%;
        margin-right: -1px;
        background: #fff;
        background-color: #0830de;
        -webkit-animation: wave 1.3s linear infinite;
        animation: wave 1.3s linear infinite;
        opacity: .6;
    }
    .text-bold-600{
        font-weight: 600;
    }

    .typing{
        position: absolute;
        bottom: 0;
        width: 100%;
        left: 0;
        text-align: right;
        padding-right: 10px;
        font-size: 11px;
        color: #08a24d;
    }

    .typing .animate-typing .dot{
        background-color: #37922d;
    }

    .typing .animate-typing{
        color: #37922d;
    }

    .rounded-avatar-what{
        height: 50px;
        border-radius: 50%;
        margin-right: 10px;
    }

  


    .bg-w{
        background-image: url({{asset('images/bg-w.png')}});
        position: absolute;
        top: 0;
        width: 100%;
        height: 100%;
        opacity: 0.7;
        background-size: 412.5px 749.25px;
        background-repeat: repeat;
    }

    .chat-text.sticker{
        background: transparent !important;
        box-shadow: unset !important;
    }

    .chat-text.sticker .imgchatwhap{
        border-radius: 10px !important;
    }

    .chat-text.sticker:before{
       display: none;
    }





    .dark-mode .bg-white{
        background-color: #262d32 !important;
    }

    .dark-mode .chat-container {
        background-color: #262d32 !important;
    }

    .dark-mode .chat-container li .chat-text {
     color: #20252a !important;
    }

    .dark-mode .users .person.active-user {
        background-color: #0b0b0b !important;
    }

    .dark-mode .selected-user {
        background: #262d31 !important;
    }

    .dark-mode .users .person p.name-time {
        border-top: 1px solid #454b51 !important;
    }

    .float-end{
        float: right;
    }

    

    .typing .nametyping{

        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
        max-width: calc(100% - 115px);
        display: inline-block;
        line-height: 11px;

    }

    .dropdown-item.active, .dropdown-item:active {
        color: #fff;
        text-decoration: none;
        background-color: #506de3;
    }

    .loader-full {
        width: 100%;
        height: 100%;
        background: #000;
        opacity: 0.8;
        z-index: 3000;
        position: fixed;
    }

    .loader-full .ico {
        top: 45%;
        color: #fff;
        position: absolute;
        z-index: 3002;
        width: 100%;
        text-align: center;
    }

    .loader-full .text {
        color: #fff;
        position: absolute;
        z-index: 3002;
        width: 100%;
        margin-top: 20px;
        text-align: center;
        font-size: 16px;
        font-weight: 600;
    }

    #mobile-indicator {
        display: none;
    }

    .chatback{
        font-size: 30px;
        background: transparent;
        color: #506de3;
        border-radius: 50%;
        position: absolute;
        cursor: pointer;
        left: 5px;
        margin-top: -25px;
        display:none;
        z-index: 2;
    }

    @media (max-width: 767.98px){
        .widget-input-container .widget-input-box {
        width: calc(100% - 120px);
        margin-left: 0px;
        }
        .nosesion{
            display:none;
        }

        #mobile-indicator {
        display: block;
        }


        .iconadjuntowa {
            font-size: 20px;
            width: 70px;
        }

    }




</style>
@endsection

@section('content')
    @if(!is_null($instancia) && !empty($instancia))
        @if($instancia->status == 0)
            <div>
                <div style="background-color: #25d366; height: 50vh"></div>
                <div
                    class="d-flex flex-md-row align-items-center bg-light flex-sm-column flex-xs-column justify-content-center"
                    style="
                    min-height: 100vh;
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 80%;
                    "
                >
                    <div class="w-100">
                    <h2 class="pl-4">
                        Para iniciar sesión en WhatsApp, debes hacer lo siguiente:
                    </h2>
                    <ul class="ml-4">
                        <li>
                        <p>Abre WhatsApp en tu teléfono</p>
                        </li>
                        <li>
                        <p>
                            Toca Menú <i class="fas fa-ellipsis-v"></i> o configuración
                            <i class="fa fa-cog" aria-hidden="true"></i> y selecciona
                            "Dispositivos vinculados"
                        </p>
                        </li>
                        <li>
                        <p>
                            Toca "Vincular un dispositivo y apunta tu teléfono hacía el
                            código qr que se muestra en pantalla para escanearlo"
                        </p>
                        </li>
                    </ul>
                    </div>
                    <div class="w-100 d-flex justify-content-center">
                        <div
                            
                        >
                            <div id="qr_code" style="position: relative;">
                                <img width="100px" src="{{asset('images/gif-tuerca.gif')}}">
                                <label style="position: absolute;top: 76%;left: 21%;">Espere...</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="background-color: gray"></div>
            </div>
        @else
        <div id="content" class="content" style="height: 100%;overflow: hidden;">
            <div id="modalinfo">
                <div id="infoModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-body">
                                
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="modaltmp">
                <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-body">
                                ${imagen}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div style="position: absolute;top: -23px;">
                <a href="javascript:;"><span onClick="showInfo()" style="font-size: small;">Info del dispositivo</span></a>
            </div>
            <div style="position: absolute;top: -23px;left: calc(100% - 140px);">
                <a href="javascript:;"><span onclick="reloadButton()" style="font-size: small;">Cerrar sesion activa</span></a>
            </div>
            <link rel="stylesheet" href="https://unpkg.com/dropzone@5/dist/min/dropzone.min.css" type="text/css">
            <div class="allcontainerchats row bg-white m-1" style="height: calc(100vh - 120px);border-radius: 5px;box-shadow: 2px 2px 10px 1px #c0bebe;">
                <div class="col-xl-4 col-lg-4 col-md-4 col-sm-3 col-12 m-0 p-0 users-container" style="height: calc(100% - 10px);">
                <div class="input-group p-3 mt-2">
                    <div style="font-size: smaller;padding-left: 12px;max-width: 38px;" class="input-group-text">
                    <span class="fas fa-search"></span>
                    </div>
                    <input style="font-size: small;" type="text" class="form-control" placeholder="Buscar.." id="searchContacto">
                    <div style="font-size: small;" class="btn-group ms-1 tpl" data-toggle="tooltip" title="" data-original-title="Ordernar chats">
                    <button style="font-size: small;padding: 6px;" class="btn btn-outline-primary btn-sm" type="button" data-toggle="dropdown" aria-expanded="false">
                        <span class="fas fa-random" style=""></span>
                        <span class="textlistorder" style="">Los mas recientes</span>
                    </button>
                    <ul style="font-size: small; max-width: 109px; position: absolute; transform: translate3d(0px, -146px, 0px); top: 0px; left: 0px; will-change: transform;" class="dropdown-menu listsorter" x-placement="top-start">
                        <li>
                        <a class="dropdown-item sorterc active" href="javascript:;">
                            <span class="nameorder">No leídos</span>
                            <span class="badge bg-pink rounded-pill">0</span>
                        </a>
                        </li>
                        <li>
                        <a class="dropdown-item sortera" href="javascript:;">
                            <span class="nameorder">Los mas recientes</span>
                        </a>
                        </li>
                        <li>
                        <a class="dropdown-item sorterb" href="javascript:;">
                            <span class="nameorder">Sin asignar</span>
                            <span class="badge bg-pink rounded-pill">1</span>
                        </a>
                        </li>
                        <li>
                        <a class="dropdown-item sorterd" href="javascript:;">
                            <span class="nameorder">Mis chats</span>
                            <span class="badge bg-pink rounded-pill">0</span>
                        </a>
                        </li>
                    </ul>
                    </div>
                    <span style="max-width: 33px;font-size: smaller;" data-toggle="tooltip" title="" class="m-l-10 " data-original-title="Nuevo Chat">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#NewContacto" style="padding-left: 37%;font-size: smaller;height: 97%;max-width: 38px;">
                        <i class="fas fa-user-plus"></i>
                    </button>
                    </span>
                </div>
                <ul class="users" id="listawhatssap">
                    @php
                        $closed = [];
                    @endphp
                    @foreach($info[0] as $chats)
                        @php
                            if($chats->estado == "cerrado"){
                                $closed[] = $chats;
                                continue;
                            }
                            $filterData = array_filter($info[1],function($item) use ($chats) {
                                return $item["id"] == $chats->asigned_to;
                            });
                            
                            $asigned = array_values($filterData)[0]["nombres"];
                            
                        @endphp
                        <li class="person" data-id="{{$chats->number}}@c.us" data-tecnico="{{$chats->asigned_to}}" data-estado="{{$chats->estado=='abierto'?0:1}}" data-time="{{strtotime($chats->last_update)}}" data-type="chat">
                                <span class="d-none"></span>
                                <div class="user" data-toggle="tooltip" title="" data-original-title="{{$chats->name}}">
                                    <img src="{{$chats->photo}}">
                                </div>
                                <p class="name-time">
                                    <span class="name">{{$chats->name}}</span>
                                    @if($chats->fromMe == "1")
                                        <span class="lastmessage truncate-text"><b>Tú: </b>@php echo $chats->last_message @endphp</span>
                                    @else
                                        <span class="lastmessage truncate-text">@php echo $chats->last_message @endphp</span>
                                    @endif
                                    <span class="operadorasignado">{{$asigned=='Sin Asignar'?'Sin Asignar':'Asignado a '.$asigned}}</span>
                                    <span class="time">{{strtotime($chats->last_update)}}</span>
                                </p>
                                <span class="conter-chat-user">{{$chats->notRead <= 0 ? "":$chats->notRead}}</span>
                                <div class="typing" style="display:none">
                                    <span class="nametyping"></span> está escribiendo <span class="animate-typing">
                                    <span class="dot"></span>
                                    <span class="dot"></span>
                                    <span class="dot"></span>
                                    </span>
                                </div>
                            </li>
                    @endforeach
                    @foreach($closed as $chats)
                        @php
                            $filterData = array_filter($info[1],function($item) use ($chats) {
                                return $item["id"] == $chats->asigned_to;
                            });
                            
                            $asigned = array_values($filterData)[0]["nombres"];
                            
                        @endphp
                        <li class="person" data-id="{{$chats->number}}@c.us" data-tecnico="{{$chats->asigned_to}}" data-estado="{{$chats->estado=='abierto'?0:1}}" data-time="{{strtotime($chats->last_update)}}" data-type="chat">
                            <span class="d-none"></span>
                            <div class="user" data-toggle="tooltip" title="" data-original-title="{{$chats->name}}">
                            <img src="{{$chats->photo}}">
                            </div>
                            <p class="name-time">
                                <span class="name">{{$chats->name}}</span>
                                @if($chats->fromMe == "1")
                                    <span class="lastmessage truncate-text"><b>Tú: </b>@php echo $chats->last_message @endphp</span>
                                @else
                                    <span class="lastmessage truncate-text">@php echo $chats->last_message @endphp</span>
                                @endif
                                <span class="operadorasignado">{{$asigned=='Sin Asignar'?'Sin Asignar':'Asignado a '.$asigned}}</span>
                                <span class="time">{{strtotime($chats->last_update)}}</span>
                            </p>
                            <span class="conter-chat-user">{{$chats->notRead <= 0 ? "":$chats->notRead}}</span>
                            <div class="typing" style="display:none">
                                <span class="nametyping"></span> está escribiendo <span class="animate-typing">
                                <span class="dot"></span>
                                <span class="dot"></span>
                                <span class="dot"></span>
                                </span>
                            </div>
                        </li>
                    @endforeach
                </ul>
                </div>
                <div class="col-xl-8 col-lg-8 col-md-8 col-sm-9 col-12 m-0 p-0 nosesion" style="background: #f8f8f8;height: 100%;">
                <div class="text-center" style="width: 100%;margin: 20% auto;">
                    <svg width="360" viewBox="0 0 303 172" fill="none" preserveAspectRatio="xMidYMid meet" class="">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M229.565 160.229c32.647-10.984 57.366-41.988 53.825-86.81-5.381-68.1-71.025-84.993-111.918-64.932C115.998 35.7 108.972 40.16 69.239 40.16c-29.594 0-59.726 14.254-63.492 52.791-2.73 27.933 8.252 52.315 48.89 64.764 73.962 22.657 143.38 13.128 174.928 2.513Z" fill="#DAF7F3"></path>
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M131.589 68.942h.01c6.261 0 11.336-5.263 11.336-11.756S137.86 45.43 131.599 45.43c-5.081 0-9.381 3.466-10.822 8.242a7.302 7.302 0 0 0-2.404-.405c-4.174 0-7.558 3.51-7.558 7.838s3.384 7.837 7.558 7.837h13.216ZM105.682 128.716c3.504 0 6.344-2.808 6.344-6.27 0-3.463-2.84-6.27-6.344-6.27-1.156 0-2.24.305-3.173.839v-.056c0-6.492-5.326-11.756-11.896-11.756-5.29 0-9.775 3.413-11.32 8.132a8.025 8.025 0 0 0-2.163-.294c-4.38 0-7.93 3.509-7.93 7.837 0 4.329 3.55 7.838 7.93 7.838h28.552Z" fill="#fff"></path>
                    <rect x=".445" y=".55" width="50.58" height="100.068" rx="7.5" transform="rotate(6 -391.775 121.507) skewX(.036)" fill="#42CBA5" stroke="#316474"></rect>
                    <rect x=".445" y=".55" width="50.403" height="99.722" rx="7.5" transform="rotate(6 -356.664 123.217) skewX(.036)" fill="#fff" stroke="#316474"></rect>
                    <path d="m57.16 51.735-8.568 82.024a5.495 5.495 0 0 1-6.042 4.895l-32.97-3.465a5.504 5.504 0 0 1-4.897-6.045l8.569-82.024a5.496 5.496 0 0 1 6.041-4.895l5.259.553 22.452 2.36 5.259.552a5.504 5.504 0 0 1 4.898 6.045Z" fill="#EEFEFA" stroke="#316474"></path>
                    <path d="M26.2 102.937c.863.082 1.732.182 2.602.273.238-2.178.469-4.366.69-6.546l-2.61-.274c-.238 2.178-.477 4.365-.681 6.547Zm-2.73-9.608 2.27-1.833 1.837 2.264 1.135-.917-1.838-2.266 2.27-1.833-.92-1.133-2.269 1.834-1.837-2.264-1.136.916 1.839 2.265-2.27 1.835.92 1.132Zm-.816 5.286c-.128 1.3-.265 2.6-.41 3.899.877.109 1.748.183 2.626.284.146-1.31.275-2.614.413-3.925-.878-.092-1.753-.218-2.629-.258Zm16.848-8.837c-.506 4.801-1.019 9.593-1.516 14.396.88.083 1.748.192 2.628.267.496-4.794 1-9.578 1.513-14.37-.864-.143-1.747-.192-2.625-.293Zm-4.264 2.668c-.389 3.772-.803 7.541-1.183 11.314.87.091 1.74.174 2.601.273.447-3.912.826-7.84 1.255-11.755-.855-.15-1.731-.181-2.589-.306-.04.156-.069.314-.084.474Zm-4.132 1.736c-.043.159-.06.329-.077.49-.297 2.896-.617 5.78-.905 8.676l2.61.274c.124-1.02.214-2.035.33-3.055.197-2.036.455-4.075.627-6.115-.863-.08-1.724-.17-2.585-.27Z" fill="#316474"></path>
                    <path d="M17.892 48.489a1.652 1.652 0 0 0 1.468 1.803 1.65 1.65 0 0 0 1.82-1.459 1.652 1.652 0 0 0-1.468-1.803 1.65 1.65 0 0 0-1.82 1.459ZM231.807 136.678l-33.863 2.362c-.294.02-.54-.02-.695-.08a.472.472 0 0 1-.089-.042l-.704-10.042a.61.61 0 0 1 .082-.054c.145-.081.383-.154.677-.175l33.863-2.362c.294-.02.54.02.695.08.041.016.069.03.088.042l.705 10.042a.61.61 0 0 1-.082.054 1.678 1.678 0 0 1-.677.175Z" fill="#fff" stroke="#316474"></path>
                    <path d="m283.734 125.679-138.87 9.684c-2.87.2-5.371-1.963-5.571-4.823l-6.234-88.905c-.201-2.86 1.972-5.35 4.844-5.55l138.87-9.684c2.874-.2 5.371 1.963 5.572 4.823l6.233 88.905c.201 2.86-1.971 5.349-4.844 5.55Z" fill="#fff"></path>
                    <path d="M144.864 135.363c-2.87.2-5.371-1.963-5.571-4.823l-6.234-88.905c-.201-2.86 1.972-5.35 4.844-5.55l138.87-9.684c2.874-.2 5.371 1.963 5.572 4.823l6.233 88.905c.201 2.86-1.971 5.349-4.844 5.55" stroke="#316474"></path>
                    <path d="m278.565 121.405-129.885 9.058c-2.424.169-4.506-1.602-4.668-3.913l-5.669-80.855c-.162-2.31 1.651-4.354 4.076-4.523l129.885-9.058c2.427-.169 4.506 1.603 4.668 3.913l5.669 80.855c.162 2.311-1.649 4.354-4.076 4.523Z" fill="#EEFEFA" stroke="#316474"></path>
                    <path d="m230.198 129.97 68.493-4.777.42 5.996c.055.781-.098 1.478-.363 1.972-.27.5-.611.726-.923.748l-165.031 11.509c-.312.022-.681-.155-1.017-.613-.332-.452-.581-1.121-.636-1.902l-.42-5.996 68.494-4.776c.261.79.652 1.483 1.142 1.998.572.6 1.308.986 2.125.929l24.889-1.736c.817-.057 1.491-.54 1.974-1.214.413-.577.705-1.318.853-2.138Z" fill="#42CBA5" stroke="#316474"></path>
                    <path d="m230.367 129.051 69.908-4.876.258 3.676a1.51 1.51 0 0 1-1.403 1.61l-168.272 11.735a1.51 1.51 0 0 1-1.613-1.399l-.258-3.676 69.909-4.876a3.323 3.323 0 0 0 3.188 1.806l25.378-1.77a3.32 3.32 0 0 0 2.905-2.23Z" fill="#fff" stroke="#316474"></path>
                    <circle transform="rotate(-3.989 1304.861 -2982.552) skewX(.021)" fill="#42CBA5" stroke="#316474" r="15.997"></circle>
                    <path d="m208.184 87.11-3.407-2.75-.001-.002a1.952 1.952 0 0 0-2.715.25 1.89 1.89 0 0 0 .249 2.692l.002.001 5.077 4.11v.001a1.95 1.95 0 0 0 2.853-.433l8.041-12.209a1.892 1.892 0 0 0-.573-2.643 1.95 1.95 0 0 0-2.667.567l-6.859 10.415Z" fill="#fff" stroke="#316474"></path>
                    </svg>
                    <div class="m-5" style="font-size: 13px;"> Selecciona un chat de la lista de contactos para iniciar una conversación. <br> Puedes Enviar imágenes,documentos,archivos utilizando el botón Adjuntar o arrastrando el archivo a la caja de escribir mensaje. </div>
                </div>
                </div>
                <span class="chatback">
                    <i class="fas fa-arrow-circle-left"></i>
                </span>
                <div class="col-xl-8 col-lg-8 col-md-8 col-sm-9 col-12 m-0 p-0 chat-container" style="display: none;height: 100%; position:relative">
                <div class="bg-w"></div>
                <div class="headerchat selected-user" style="display: none">
                    <a class="chat-back p-r-5 p-l-5 m-r-5">
                    <span class="f-s-20 ti-arrow-left"></span>
                    </a>
                    <span>
                        <img src="https://www.bootdey.com/img/Content/avatar/avatar3.png">
                        <span class="name" contenteditable="true" style="padding: 10px;"></span>
                    </span>
                    <div class="float-end" data-toggle="tooltip" title="" data-original-title="Marcar como cerrado el  Chat">
                        <span style="padding-left: 8px;" class="closechatw fas fa-user-times" id="closedchat"></span>
                    </div>
                    <div class="float-end">
                        <a class="p-0 m-0" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <span style="padding-left: 10px;" class="fas fa-user closechatw fa-lg" data-toggle="tooltip" title="" data-original-title="Asignar operador al Chat"></span>
                    </a>
                    <div class="dropdown-menu listaoperadores" style="line-height: normal;"> 
                    </div>
                    </div>
                </div>
                <div class="messageasigned">Chat asignado a <b class="nameoperador"></b>
                </div>
                <ul class="chat-box chatContainerScroll"></ul>
                <div id="containertgs"></div>
                <div id="showtyping" style="display: none" class="">
                    <span class="typingoperador text-bold-600"></span> Está escribiendo <span class="animate-typing">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                    </span>
                </div>
                <div class="widget-input widget-input-rounded barrasend bg-white" style="padding-top: 15px;">
                    <input type="hidden" id="urlfile" value="/tmp/phpC6qOA3">
                    <input type="hidden" id="mimefile" value="image/jpeg">

                    <div class="widget-input-container" style="position:relative">
                        <div class="d-none" id="uploads_files" style="width: 170px;position: absolute;height: 170px;border-width: 2px;border-radius: 21px;top: -187px;left: 2px;border-style: dotted;background-color: darkgrey;">
                            <div id="removeFile" style="position:relative; top:0px;">
                                <i class="fa fa-times-circle " style="position: absolute;right: 6px;top: 7px;"></i>
                            </div>
                            <div id="previewFile" style="padding-left: 21px;padding-top: 13px;">
                            </div>
                            <div id="nameFile" style="font-size: small;position: relative;top: -15px;text-align: center;left: 0px;">descarga.jpeg</div>
                        </div>
                        <a class="iconadjuntowa"><i class="fas fa-upload"></i></a>
                        <div class="widget-input-icon waadjunto dz-clickable dz-started dz-max-files-reached">
                        </div>
                        <div class="widget-input-box p-auto m-auto">
                            <textarea style="margin-top: 0px; padding-top:20px; padding-right:15px;" type="text" class="form-control" placeholder="Enviar mensaje" id="sendNewMessage"></textarea>
                        </div>
                        <div class="widget-input-icon d-none">
                            <a href="#" class="text-grey">
                                <i class="fa fa-smile"></i>
                            </a>
                        </div>
                        <div class="widget-input-divider d-none"></div>
                        <div class="widget-input-icon mt-2">
                            <a href="javascript:;" class="text-grey record-wa" style="display: none;"><i class="fa f-s-25 fa-microphone fa-2x"></i></a>
                            <a href="javascript:;" class="text-red stop-wa parpadea" style="display: none;"><i class="fas f-s-25 fa-stop fa-2x"></i></a>
                        </div>
                    </div>
                </div>
            </div>
            <span id="mobile-indicator"></span>
            <!-- Modal -->
            <div class="modal fade" id="NewContacto" tabindex="-1" style="display: none;" aria-hidden="true">
                <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Nuevo Chat</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                    </div>
                    <div class="modal-body">
                    <div class="form-group">
                        <label for="exampleInputEmail1">Número Teléfono</label>
                        <input type="text" class="form-control" id="numbersender" placeholder="51989876567">
                        <small>* Debe ingresar el número de teléfono en formato internacional</small>
                    </div>
                    <div class="form-group">
                        <label for="exampleInputPassword1">Mensaje</label>
                        <textarea id="msjnewchat" rows="5" placeholder="Mensaje" class="form-control"></textarea>
                    </div>
                    </div>
                    <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                    <button type="button" id="sendnewchat" class="btn btn-primary">Enviar mensaje</button>
                    </div>
                </div>
                </div>
            </div>
        </div>
        @endif
    @else
        
        <div id="content" class="content" style="height:80vh;overflow: auto;">
            <div id="alert_created" class="alert alert-warning" style="text-align: center;">
                Usted no tiene una instancia creada, Escriba su IP o DNS y haga click en el boton.
            </div>
            <div style="text-align: center;">
                <div class="row mb-5 mt-4">
                    <div style="text-align:right" class="col-4 m-auto">
                        <span>Escriba aqui su IP o DNS</span>
                    </div>
                    <div class="col-4">
                        <input type="text" id="ipAddr" class="form-control">
                    </div>
                    <div style="text-align:left" class="col-3">
                        <button onClick="addAutomaticIP()" class="btn btn-warning">Rellenar</button>
                    </div>
                </div>
                <button onClick="createInstancia()" class="btn btn-success">Crear Instancia</button>
            </div>
        </div>
    @endif
@endsection
@section("scripts")
<script src="https://cdn.jsdelivr.net/npm/opus-media-recorder@latest/OpusMediaRecorder.umd.js"></script>
<!-- load encoderWorker.umd.js. This should be after OpusMediaRecorder. -->
<!-- This script tag will create OpusMediaRecorder.encoderWorker. -->
<script src="https://cdn.jsdelivr.net/npm/opus-media-recorder@latest/encoderWorker.umd.js"></script>
<script>
    @if(!is_null($instancia) && !empty($instancia))
        @if($instancia->status == 0)
     
            let temporizador;
            function getChats(){
                $("#contenedor_carga").css("visibility","visible").css("opacity","1").prepend("<div style=\"text-align: center;margin-top: 50px;font-size: larger;font-weight: 900;\">Obteniendo chats, por favor espere.(<span id='chatsReady'>0</span>/<span id='chatsTotal'>0</span>)</div>");
                socketSerVER.on('totalChats', function(data) {
                    $("#chatsTotal").html(data);
                });
                socketSerVER.on('countChats', function(data) {
                    $("#chatsReady").html(data);
                });
                try{
                    $.post("{{route('crm.whatsapp')}}",{action:"getChats",_token})
                    .then((data)=>{
                        if(typeof data == "string"){
                            data = JSON.parse(data);
                        }
                        $("#contenedor_carga").css("visibility","hidden").css("opacity","0").html(`<img id="carga" src="{{asset('images/gif-tuerca.gif')}}">`);
                        swal({
                                title: data.message,
                                type: data.salida,
                                showCancelButton: false,
                                showConfirmButton: true,
                                cancelButtonColor: '#00ce68',
                                cancelButtonText: 'Aceptar',
                            }).then((result) => {
                                if(data.salida == "success"){
                                    location.reload();
                                }
                            });
                        
                    })
                }catch(err){
                    $("#contenedor_carga").css("visibility","hidden").css("opacity","0").html(`<img id="carga" src="{{asset('images/gif-tuerca.gif')}}">`);
                    swal({
                            title: "Ocurrio un error obteniendo los chats de la instancia, comunicate con tu proveedor.",
                            type: "error",
                            showCancelButton: false,
                            showConfirmButton: true,
                            cancelButtonColor: '#00ce68',
                            cancelButtonText: 'Aceptar',
                    });
                }
            }
            function reloadButton(){
                $("#contenedor_carga").css("visibility","visible").css("opacity","1").prepend("<div style=\"text-align: center;margin-top: 50px;font-size: larger;font-weight: 900;\">Reiniciando la instancia, por favor espere.</div>");
                try{
                    $.post("{{route('crm.whatsapp')}}",{action:"reloadInstancia",_token})
                    .then((data)=>{
                        if(typeof data == "string"){
                            data = JSON.parse(data);
                        }
                        $("#contenedor_carga").css("visibility","hidden").css("opacity","0").html(`<img id="carga" src="{{asset('images/gif-tuerca.gif')}}">`);
                        swal({
                                title: data.message,
                                type: data.salida,
                                showCancelButton: false,
                                showConfirmButton: true,
                                cancelButtonColor: '#00ce68',
                                cancelButtonText: 'Aceptar',
                            });
                        if(data.salida == "success"){
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        }
                    })
                }catch(err){
                    $("#contenedor_carga").css("visibility","hidden").css("opacity","0").html(`<img id="carga" src="{{asset('images/gif-tuerca.gif')}}">`);
                    swal({
                            title: "Ocurrio un error reiniciando la instancia, comunicate con tu proveedor.",
                            type: "error",
                            showCancelButton: false,
                            showConfirmButton: true,
                            cancelButtonColor: '#00ce68',
                            cancelButtonText: 'Aceptar',
                        });
                }
            }
            function putReload(){
                $("#qr_code").html(`
                <button id="reloadButton" onClick="reloadButton()" class="p-4 rounded-circle">
                    <svg fill="#00000073" height="100px" width="100px" version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 489.698 489.698" xml:space="preserve">
                        <g>
                            <g>
                                <path d="M468.999,227.774c-11.4,0-20.8,8.3-20.8,19.8c-1,74.9-44.2,142.6-110.3,178.9c-99.6,54.7-216,5.6-260.6-61l62.9,13.1
                                    c10.4,2.1,21.8-4.2,23.9-15.6c2.1-10.4-4.2-21.8-15.6-23.9l-123.7-26c-7.2-1.7-26.1,3.5-23.9,22.9l15.6,124.8
                                    c1,10.4,9.4,17.7,19.8,17.7c15.5,0,21.8-11.4,20.8-22.9l-7.3-60.9c101.1,121.3,229.4,104.4,306.8,69.3
                                    c80.1-42.7,131.1-124.8,132.1-215.4C488.799,237.174,480.399,227.774,468.999,227.774z"></path>
                                <path d="M20.599,261.874c11.4,0,20.8-8.3,20.8-19.8c1-74.9,44.2-142.6,110.3-178.9c99.6-54.7,216-5.6,260.6,61l-62.9-13.1
                                    c-10.4-2.1-21.8,4.2-23.9,15.6c-2.1,10.4,4.2,21.8,15.6,23.9l123.8,26c7.2,1.7,26.1-3.5,23.9-22.9l-15.6-124.8
                                    c-1-10.4-9.4-17.7-19.8-17.7c-15.5,0-21.8,11.4-20.8,22.9l7.2,60.9c-101.1-121.2-229.4-104.4-306.8-69.2
                                    c-80.1,42.6-131.1,124.8-132.2,215.3C0.799,252.574,9.199,261.874,20.599,261.874z"></path>
                            </g>
                        </g>
                    </svg>
                </button>
                <label style="position: absolute;left: 3px;top: 100%;font-weight: 500;width: 200px;padding-top: 4px;">Reiniciar Instancia</label>
                `)
            }
            function reiniciarTemporizador() {
                timeout = 120000;
                clearTimeout(temporizador);
                temporizador = setTimeout(putReload, timeout);
            }
            
            reiniciarTemporizador();
            
           

            socketSerVER.on('qr', function(data) {
                
                reiniciarTemporizador();
                $("#qr_code").html(`<img  src="${data}" width="300px" alt="código qr de WhatsApp" />`)
            });

            socketSerVER.on('ready', async function(data) {
                await getChats()
            });
                    
        @else            
            function getChats(){
                $("#contenedor_carga").css("visibility","visible").css("opacity","1").prepend("<div style=\"text-align: center;margin-top: 50px;font-size: larger;font-weight: 900;\">Obteniendo chats, por favor espere.(<span id='chatsReady'>0</span>/<span id='chatsTotal'>0</span>)</div>");
                socketSerVER.on('totalChats', function(data) {
                    $("#chatsTotal").html(data);
                });
                socketSerVER.on('countChats', function(data) {
                    $("#chatsReady").html(data);
                });
                try{
                    $.post("{{route('crm.whatsapp')}}",{action:"getChats",_token})
                    .then((data)=>{
                        if(typeof data == "string"){
                            data = JSON.parse(data);
                        }
                        $("#contenedor_carga").css("visibility","hidden").css("opacity","0").html(`<img id="carga" src="{{asset('images/gif-tuerca.gif')}}">`);
                        swal({
                                title: data.message,
                                type: data.salida,
                                showCancelButton: false,
                                showConfirmButton: true,
                                cancelButtonColor: '#00ce68',
                                cancelButtonText: 'Aceptar',
                            }).then((result) => {
                                if(data.salida == "success"){
                                    location.reload();
                                }
                            });
                        
                    })
                }catch(err){
                    $("#contenedor_carga").css("visibility","hidden").css("opacity","0").html(`<img id="carga" src="{{asset('images/gif-tuerca.gif')}}">`);
                    swal({
                            title: "Ocurrio un error obteniendo los chats de la instancia, comunicate con tu proveedor.",
                            type: "error",
                            showCancelButton: false,
                            showConfirmButton: true,
                            cancelButtonColor: '#00ce68',
                            cancelButtonText: 'Aceptar',
                    });
                }
            }
            let ishttps;
            
            var myDropzone;
            var timertyping;
            var operadores = {
                @foreach($info[1] as $user)
                    "{{$user['id']}}":"{{$user['nombres']}}",
                @endforeach
            };
            $.each(operadores, function(key, value) {
                $('.listaoperadores').append('<a class="dropdown-item itemoperador m-t-3 m-b-3" data-asignado="'+key+'"><span class="fas fa-chevron-right"> </span>'+value+'</a>');
            });
            var typechats = {
                "video": '<span class="fas fa-video fa-lg"></span> Video',
                "ptt": '<span class="fas fa-microphone fa-lg"></span> Audio',
                "audio": '<span class="fas fa-microphone fa-lg"></span> Audio',
                "image": '<span class="fas fa-image fa-lg"></span> Imagen',
                "sticker": '<span class="fas fa-file fa-lg"></span> Sticker',
                "document": '<span class="fas fa-file-archive fa-lg"></span> Archivo',
                "location": '<span class="fas fa-map fa-lg"></span> Ubicacion'
            }
            var typechatsalerta = {
                "video": 'Ha enviado  <span class = "fas fa-video fa-lg" ></span> Video',
                "ptt": 'Ha enviado  <span class = "fas fa-microphone fa-lg" ></span> Audio',
                "audio": 'Ha enviado  <span class = "fas fa-microphone fa-lg" ></span> Audio',
                "image": 'Ha enviado  <span class = "fas fa-image fa-lg" ></span> Imagen',
                "sticker": 'Ha enviado  <span class = "fas fa-file fa-lg" ></span> Sticker',
                "document": 'Ha enviado  <span class = "fas fa-file-archive fa-lg" ></span> Archivo',
                "location": 'Ha enviado  <span class = "fas fa-map fa-lg" ></span> Ubicacion'
            }

            function conterunread() {
                var conterunreadchat = 0;
                $('.conter-chat-user').each(function() {
                    if (!$(this).is(':empty')) {
                    conterunreadchat += 1;
                    }
                });
                if (conterunreadchat > 0) {
                    $('title').html("(" + conterunreadchat + ") Whatsapp Messages");
                } else {
                    $('title').html("Whatsapp Messages");
                }
                unreadwhat();
            }
            
            function contermischats() {
                $('.sorterd .badge').text($('li.person[data-tecnico="{{$user = Auth::user()->id}}"]').length);
                $('.sorterc .badge').text($('li.person .conter-chat-user:not(:empty)').length);
                $('.sorterb .badge').text($('li.person[data-tecnico="0"]').length);
            }
            function imgcharged() {
                $('.chatContainerScroll').animate({
                    scrollTop: $('.chatContainerScroll').prop("scrollHeight")
                });
            }

            function unreadwhat() {}

            function actulizartime() {
                $('li .chat-hour').each(function() {
                    $('.timeelapsed', this).text(timelapwa($(this).attr('data-time')));
                });
                $('.person').each(function() {
                    $('.time', this).text(timelapwa($(this).attr('data-time')));
                });
            }

            function timelapwa(ptime) {
                var etime = (Date.now() / 1000 | 0)-ptime;
                if (etime < 1) {
                    return '0 segundos';
                }
                var a = {
                    '31536000': 'año',
                    '2592000': 'mes',
                    '86400': 'dia',
                    '3600': 'hora',
                    '60': 'minuto',
                    '1': 'segundo'
                };
                var a_plural = {
                    'año': 'Años',
                    'mes': 'meses',
                    'dia': 'dias',
                    'hora': 'horas',
                    'minuto': 'minutos',
                    'segundo': 'segundos'
                };
                var output = '';
                $.each(a, function(secs, str) {
                    var d = etime / secs;
                    if (d >= 1) {
                    var r = Math.round(d);
                    output = "hace " + r + ' ' + (r > 1 ? a_plural[str] : str);
                    return true;
                    }
                });
                return output;
            }
            $(document).ready(function() {

                conterunread();
                contermischats();
                actulizartime();
                socketSerVER.on('qr', function(data) {
                    setTimeout(() => {
                        location.reload();
                    }, 2000);
                });
                $('.selected-user .name').on('keypress', function(e) {
                    if (e.which == 13 && $(this).text() !== '') {
                        $.post("{{route('crm.whatsapp')}}", {
                            action:"changeName",
                            _token,
                            id: $('.person.active-user').data('id'),
                            nombre: $(this).text()
                        });
                        e.preventDefault();
                        $('#sendNewMessage').focus();
                        socketSerVER.emit("typing", {
                            "changename": "true",
                            "id": $('.person.active-user').data('id'),
                            "nombre": $(this).text()
                        });
                        return;
                    }
                });
                    
                Dropzone.autoDiscover = false;
                const record = document.querySelector('.record-wa');
                const stop = document.querySelector('.stop-wa');
                window.location.protocol==='https:'?(ishttps = true):(ishttps = false);
                if (ishttps) {
                    $('.record-wa').show();
                    const workerOptions = {
                        OggOpusEncoderWasmPath: 'https://cdn.jsdelivr.net/npm/opus-media-recorder@latest/OggOpusEncoder.wasm',
                        WebMOpusEncoderWasmPath: 'https://cdn.jsdelivr.net/npm/opus-media-recorder@latest/WebMOpusEncoder.wasm'
                    };

                    if (navigator.mediaDevices.getUserMedia) {
                        const constraints = {
                            audio: true
                        };
                        let chunks = [];
                        let onSuccess = function(stream) {
                            window.MediaRecorder = OpusMediaRecorder;
                            const mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/ogg' }, workerOptions);
                            //const mediaRecorder = new MediaRecorder(stream);
                            record.onclick = function() {
                                mediaRecorder.start();
                                $('.record-wa').hide();
                                $('.stop-wa').show();
                                socketSerVER.emit("typing", {
                                    "id": $('.person.active-user').data('id'),
                                    "tecnico": "{{$user = Auth::user()->id}}",
                                    "name": "{{$user = Auth::user()->nombres}}"
                                });
                            }
                            stop.onclick = function() {
                                mediaRecorder.stop();
                                $('.record-wa').show();
                                $('.stop-wa').hide();
                            }
                            mediaRecorder.ondataavailable = function(e) {
                                e.data.name = 'record.ogg';
                                myDropzone.addFile(e.data);
                            }
                        }
                        let onError = function(err) {
                            $('.record-wa').hide();
                            console.log('Error permisos: ' + err);
                        }
                        navigator.mediaDevices.getUserMedia(constraints).then(onSuccess, onError);
                    }
                } else {
                    $('.record-wa').hide();
                }
                    //UPLOAD FILES       
                $('#sendNewMessage').on("dragover", function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                });
                $('#sendNewMessage').on("dragleave", function(event) {
                    event.preventDefault();
                    event.stopPropagation();
                });
                $('#sendNewMessage').on('paste', function(event) {
                    var items = (event.clipboardData || event.originalEvent.clipboardData).items;
                    for (index in items) {
                    var item = items[index];
                    if (item.kind === 'file') {
                        myDropzone.addFile(item.getAsFile())
                    }
                    }
                });
                myDropzone = new Dropzone("div.waadjunto", {
                    maxFiles: 1,
                    acceptedFiles: "image/*,application/*,text/plain,audio/*,video/*",
                    url: "{{route('uploadFile')}}",
                    sending: function(data, xhr, formData) {
                        $(".iconadjuntowa").html('<i class="fa fa-spinner fa-spin"></i>');
                    },
                    init: function() {
                        this.on("error", function(file, errorMessage) {
                            $(".iconadjuntowa").html('<i class="fas fa-upload"></i>');
                            this.removeFile(file);
                            swal({
                                title: "No se permiten ese tipo de archivos.",
                                type: "error",
                                showCancelButton: false,
                                showConfirmButton: true,
                                cancelButtonColor: '#00ce68',
                                cancelButtonText: 'Aceptar',
                            });
                            
                        });
                    }
                    
                });
                myDropzone.on("removedfile", function(file) {
                    $('#urlfile').val('');
                    $('#mimefile').val('');
                    $(".iconadjuntowa").html('<i class="fas fa-upload"></i>');
                    $("#uploads_files").addClass("d-none");
                });
                myDropzone.on("success", function(file, response) {
                    let name = file.name.split(".");
                    let extension = name[name.length - 1];
                    name = name[0].substr(0,13)+"."+extension;
                    $("#nameFile").html(name);
                    $("#uploads_files").removeClass("d-none");

                    $(".iconadjuntowa").html('<i class="fas fa-upload"></i>');
                    if($(".dz-image img").attr("src")){
                        let preview = $(".dz-image").html();
                        $('#previewFile').html(preview);
                    }else{
                        $('#previewFile').html('<i class="fas fa-file fa-5x" style="padding-left: 31px;padding-top: 17px;"></i>');
                    }


                    $(".dz-success").remove()

                    $('#sendNewMessage').focus();

                    $("#removeFile").on("click",()=>{
                        myDropzone.removeFile(file);
                    })

                    if (response.estado) {
                        $('#urlfile').val(response.name);
                        $('#mimefile').val(response.mime);
                        if ($('#mobile-indicator').is(':visible')) {
                            var e = $.Event("keypress", {
                            which: 13
                            });
                            $('#sendNewMessage').trigger(e);
                        }
                    }
                });
                $("#closedchat").on("click", function() {
                    $.post("{{route('crm.whatsapp')}}", {
                        _token,
                        action:"closeChat",
                        id: $('.person.active-user').attr("data-id")
                    });
                    $('.itemoperador[data-asignado="0"]').click();
                    $('.person.active-user').attr("data-estado", "1");
                    $('.users').find('li.active-user').appendTo('.users');
                    setTimeout(() => {
                    contermischats();
                    }, 300);
                });
                    //ORDENA MAS RECIENTE
                $(document).on("click", ".sortera", function() {
                    $('.listsorter a').removeClass("active");
                    $(this).addClass("active");
                    $('.textlistorder').text($('.nameorder', this).text());
                    setTimeout(function() {
                    $('.users .person').sort((a, b) => {
                        return $(a).attr("data-estado")-$(b).attr("data-estado") || $(b).attr("data-time")-$(a).attr("data-time");
                    }).appendTo('.users');
                    }, 300);
                });
                    //ORDENA SIN ASIGNAR PRIMERO
                $(document).on("click", ".sorterb", function() {
                    $('.listsorter a').removeClass("active");
                    $(this).addClass("active");
                    $('.textlistorder').text($('.nameorder', this).text());
                    setTimeout(function() {
                    $('.users .person').sort((a, b) => {
                        return $(a).attr("data-estado")-$(b).attr("data-estado") || $(a).attr("data-tecnico")-$(b).attr("data-tecnico") || $(b).attr("data-time")-$(a).attr("data-time");
                    }).appendTo('.users');
                    }, 300);
                });
                $(document).on("click", "#sendnewchat", function() {
                    if ($('#numbersender').val() == '') {
                        alerta("error", "Debe indicar un número correcto");
                    }
                    if ($('#msjnewchat').val() == '') {
                        alerta("error", "Debe indicar un mensaje");
                    }
                    $('#NewContacto').modal('hide');
                    $('.loader-full').show();
                    $.post("{{route('crm.whatsapp')}}", {
                        _token,
                        action:"sendMessage",
                        id: $('#numbersender').val() + '@c.us',
                        message: $('#msjnewchat').val(),
                        tipo: 'texto'
                        }).done(function(data) {
                            if(typeof data == "string"){
                                data = JSON.parse(data);
                            }
                            if(data.salida == "error"){
                                swal({
                                    title: data.message,
                                    type: data.salida,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    cancelButtonColor: '#00ce68',
                                    cancelButtonText: 'Aceptar',
                                }).then((result) => {
                                });
                            }
                            $('#numbersender').val('');
                            $('#msjnewchat').val('');
                            contermischats();
                            actulizartime();
                            //getallchats();
                        });
                    });
                    //ORDENA SIN LEER PRIMERO
                $(document).on("click", ".sorterc", function() {
                    $('.listsorter a').removeClass("active");
                    $(this).addClass("active");
                    $('.textlistorder').text($('.nameorder', this).text());
                    setTimeout(function() {
                    $('.users .person').sort((a, b) => {
                        return $('.conter-chat-user', b).text()-$('.conter-chat-user', a).text()
                    }).appendTo('.users');
                    }, 300);
                });
                //ORDENA MIS CHATS
                $(document).on("click", ".sorterd", function() {
                    $('.listsorter a').removeClass("active");
                    $(this).addClass("active");
                    $('.textlistorder').text($('.nameorder', this).text());
                    setTimeout(function() {
                    $   ('.users').prepend($('.users .person[data-tecnico="{{$user = Auth::user()->id}}"]'));
                    }, 100);
                });
                //BUSCAR CONTACTOS
                $("#searchContacto").on("keyup", function() {
                    var value = this.value.toLowerCase().trim();
                    $("li.person").css("display", "list-item").filter(function() {
                    return $(this).text().toLowerCase().trim().indexOf(value) == -1;
                    }).css("display", "none");
                });
                $(document).on({
                    mouseenter: function() {
                        if ($('.lastmessage', this).text()) {
                            $(this).tooltip({
                            title: $('.lastmessage', this).text(),
                            html: true,
                            container: '.users'
                            }).tooltip('show');
                        }
                    },
                    mouseleave: function() {}
                }, '.users li[data-type="chat"]');

                $('#content').on("click", ".chatback", function() {
                    $('.listaoperadores .itemoperador.active').removeClass('active');
                    $('.users-container .person').removeClass('active-user');
                    $('.chat-container').hide();
                    $('.users-container').show();
                    $('.chatback').hide();
                })

                $('#content').on("click", ".users-container .person", function() {
                    if ($('#mobile-indicator').is(':visible')) {
                        $('.users-container').hide();
                        $('.chatback').show();
                    }
                    $('.chat-container').show();
                    $('.nosesion').hide();
                    $('#urlfile').val('');
                    $('#mimefile').val('');
                    $('#showtyping').hide();
                    $('.listaoperadores .itemoperador.active').removeClass('active');
                    $('.listaoperadores a[data-asignado="' + $(this).attr('data-tecnico') + '"]').addClass('active');
                    if ($(this).attr('data-tecnico') > 0) {
                        $('.messageasigned').show();
                        $('.messageasigned .nameoperador').text(operadores[$(this).attr('data-tecnico')]);
                    } else {
                        $('.messageasigned').hide();
                        $('.messageasigned .nameoperador').text('');
                    }
                    $('.users-container .person').removeClass('active-user');
                    $(this).addClass('active-user');
                    $('.selected-user,.chat-container').hide();
                    $('.loader-full').show();
                    if ($(window).width() < 768) {
                        $('.col-chats').hide();
                    }
                    $('.chatContainerScroll').empty();
                    $('.selected-user img').attr('src', $('img', this).attr('src'));
                    $('.selected-user .name').html($('.name', this).html());
                    $('.selected-user').show();
                    var idchatpersona = $(this).data('id');
                    $("#contenedor_carga").css("visibility","visible").css("opacity","1").prepend("<div style=\"text-align: center;margin-top: 50px;font-size: larger;font-weight: 900;\">Obteniendo mensajes, por favor espere.</div>");
                    $.post("{{route('crm.whatsapp')}}",{action:"getChat",_token,id:idchatpersona,"limit":"20"})
                    .then((data)=>{
                        if(typeof data == "string"){
                            data = JSON.parse(data);
                        }
                       
                        if(data.salida == "success"){
                            if(typeof data?.messages == "string"){
                                data.messages = JSON.parse(data.messages);
                            }
                            let mensajes =  data.messages ;
                            $('.loader-full').hide();
                            $('.chat-container').show();
                            $('.barrasend input').focus();

                            for (let i = 0; i < mensajes.length; i++) {
                               
                                let datos = mensajes[i];
                                let ubicacion;
                                datos.fromMe ? ubicacion = "chat-right" : ubicacion = "chat-left";
                                if(datos.hasMedia){
                                    
                                    datos.type == "ptt"?datos.type = "audio":datos.type = datos.type;
                                    datos.type == "sticker"?datos.type = "image":datos.type = datos.type;
                                    switch(datos.type){
                                        case "document":
                                            data =`
                                            <li class="${ubicacion}">
                                                <div class="chat-text">
                                                    <div >
                                                        <div id='${datos.id.id}'>
                                                            <i onclick="verContenido('document','${datos.id.id}','${_token}','${datos._data.mimetype}')" class="fas fa-file-archive fa-5x" style="margin-left: 27px;"></i>
                                                        </div>
                                                        <small style='margin-left: 31px;'>Click 👆👆</small>
                                                    </div>
                                                    ${datos.body}
                                                    <div class="chat-hour" data-time="${datos.timestamp}">
                                                        <span class="timeelapsed">hace 18 horas</span>
                                                        <span class="${datos.id.id} d-none check-w-0">
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                            `; 
                                            $(".chatContainerScroll").append(data);
                                            
                                            break;
                                        case "audio":
                                            data =`
                                            <li class="${ubicacion}">
                                                <div class="chat-text">
                                                    <div >
                                                        <div id='${datos.id.id}'>
                                                            <i onclick="verContenido('audio','${datos.id.id}','${_token}','${datos._data.mimetype}')" class="fas fa-microphone fa-5x" style="margin-left: 27px;"></i>
                                                        </div>
                                                        <small style='margin-left: 31px;'>Click 👆👆</small>
                                                    </div>
                                                    ${datos.body}
                                                    <div class="chat-hour" data-time="${datos.timestamp}">
                                                        <span class="timeelapsed">hace 18 horas</span>
                                                        <span class="${datos.id.id} d-none check-w-0">
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                            `; 
                                            $(".chatContainerScroll").append(data);
                                            
                                            break;
                                        case "image":
                                            data =`
                                            <li class="${ubicacion}">
                                                <div class="chat-text">
                                                    <div >
                                                        <div id='${datos.id.id}'>
                                                            <i onclick="verContenido('imagen','${datos.id.id}','${_token}','${datos._data.mimetype}')" class="fas fa-image fa-5x" style="margin-left: 27px;"></i>
                                                        </div>
                                                        <small style='margin-left: 31px;'>Click 👆👆</small>
                                                    </div>
                                                    ${datos.body}
                                                    <div class="chat-hour" data-time="${datos.timestamp}">
                                                        <span class="timeelapsed">hace 18 horas</span>
                                                        <span class="${datos.id.id} d-none check-w-0">
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                            `; 
                                            $(".chatContainerScroll").append(data);
                                            
                                            break;
                                        case "video":
                                            data =`
                                            <li class="${ubicacion}">
                                                <div class="chat-text">
                                                    <div >
                                                        <div id='${datos.id.id}'>
                                                            <i onclick="verContenido('video','${datos.id.id}','${_token}','${datos._data.mimetype}')" class="fas fa-video fa-5x" style="margin-left: 27px;"></i>
                                                        </div>
                                                        <small style='margin-left: 31px;'>Click 👆👆</small>
                                                    </div>
                                                    ${datos.body}
                                                    <div class="chat-hour" data-time="${datos.timestamp}">
                                                        <span class="timeelapsed">hace 18 horas</span>
                                                        <span class="${datos.id.id} d-none check-w-0">
                                                        </span>
                                                    </div>
                                                </div>
                                            </li>
                                            `; 
                                            $(".chatContainerScroll").append(data);
                                            
                                            break;
                                        default:
                                            
                                            break;
                                    }
                                }else{

                                    if(datos.type == "location"){
                                        data =`
                                        <li class="${ubicacion}">
                                            <div class="chat-text">
                                                <div >
                                                    <div id='${datos.id.id}'>
                                                        <a target='_blank' href="https://www.google.com/maps?q=${datos.location.latitude},${datos.location.longitude}">
                                                            <i class="fas fa-map fa-5x" style="margin-left: 27px;"></i>
                                                        </a>
                                                    </div>
                                                    <small style='margin-left: 31px;'>Click 👆👆</small>
                                                </div>
                                                <div class="chat-hour" data-time="${datos.timestamp}">
                                                    <span class="timeelapsed">hace 18 horas</span>
                                                    <span class="${datos.id.id} d-none check-w-0">
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                        `; 
                                        $(".chatContainerScroll").append(data);
                                    }else{
                                        let data =`
                                        <li class="${ubicacion}">
                                            <div class="chat-text">
                                                ${datos.body}
                                                <div class="chat-hour" data-time="${datos.timestamp}">
                                                    <span class="timeelapsed">hace 18 horas</span>
                                                    <span class="${datos.id.id} d-none check-w-0">
                                                    </span>
                                                </div>
                                            </div>
                                        </li>
                                        `; 
                                        $(".chatContainerScroll").append(data);
                                        
                                    }
                                }
                                
                            }
                            $('.chatContainerScroll').scrollTop($('.chatContainerScroll').prop("scrollHeight"));
                            $("#contenedor_carga").css("visibility","hidden").css("opacity","0").html(`<img id="carga" src="{{asset('images/gif-tuerca.gif')}}">`);
                            
                            
                           
                            
                        }else{
                            swal({
                                title: data.message,
                                type: data.salida,
                                showCancelButton: false,
                                showConfirmButton: true,
                                cancelButtonColor: '#00ce68',
                                cancelButtonText: 'Aceptar',
                            }).then((result) => {
                                if(data.salida == "success"){
                                    location.reload();
                                }
                            });
                        }
                        
                        conterunread();
                        actulizartime();
                        contermischats()
                    })
                });

                $('#sendNewMessage').on('keypress', function(e) {
                    if (e.keyCode === 13 && e.shiftKey) {
                        return;
                    }
                    if (e.which == 13 && $('#urlfile').val().length > 0) {
                        e.preventDefault();
                        $('.loader-full').show();
                        let mensaje = $('#sendNewMessage').val();
                        let file = $('#urlfile').val();
                        let mimetype = $('#mimefile').val();
                        $('.loader-full').hide();
                        myDropzone.removeAllFiles(true);
                        $('#urlfile').val('');
                        $('#mimefile').val('');
                        $("#uploads_files").addClass("d-none");
                        $('#sendNewMessage').val('');
                        $.post("{{route('crm.whatsapp')}}", {
                            action:"sendFile",
                            _token,
                            "id": $('.person.active-user').data('id'),
                            "file": file,
                            "mime": mimetype,
                            "mensaje": mensaje,
                            "tecnico": "{{$user = Auth::user()->id}}",
                            "name": "{{$user = Auth::user()->nombres}}"
                        }).done(function(data) {
                            if(typeof data == "string"){
                                data = JSON.parse(data);
                            }
                            if(data.salida == "error"){
                                swal({
                                    title: data.message,
                                    type: data.salida,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    cancelButtonColor: '#00ce68',
                                    cancelButtonText: 'Aceptar',
                                })
                            }

                            $('.users li[data-id="' + data.from + '"]').attr('data-time', data.timestamp).attr('data-estado', "0").prependTo(".users");
                            $('.users li[data-id="' + data.from + '"] .lastmessage').html("<b>Tú: </b>"+data.body);
                            contermischats();
                            actulizartime();
                        });
                        return;
                    }
                    if (e.which == 13 && $(this).val().length > 0) {
                        e.preventDefault();
                        $('#urlfile').val('');
                        $('#mimefile').val('');
                        let mensaje =  $(this).val();
                        $(this).val('');
                        $.post("{{route('crm.whatsapp')}}", {
                            _token,
                            action:"sendMessage",
                            id: $('.person.active-user').data('id'),
                            message:mensaje,
                            tipo: 'texto'
                        }).done(function(data) {
                            if(typeof data == "string"){
                                data = JSON.parse(data);
                            }
                            $('.users li[data-id="' + data.from + '"]').attr('data-time', data.timestamp).attr('data-estado', "0").prependTo(".users");
                            $('.users li[data-id="' + data.from + '"] .lastmessage').html("<b>Tú: </b>"+data.body);
                            if(data.salida != "success"){
                                swal({
                                    title: data.message,
                                    type: data.salida,
                                    showCancelButton: false,
                                    showConfirmButton: true,
                                    cancelButtonColor: '#00ce68',
                                    cancelButtonText: 'Aceptar',
                                }).then((result) => {
                                    if(data.salida == "success"){
                                        location.reload();
                                    }
                                });
                            }
                            contermischats();
                            actulizartime();
                        });
                       
                        return;
                    }
                
                    socketSerVER.emit("typing", {
                        "id": $('.person.active-user').data('id'),
                        "tecnico": "{{$user = Auth::user()->id}}",
                        "name": "{{$user = Auth::user()->nombres}}"
                    });
                });
                socketSerVER.io.on("error", (error) => {
                    console.log("ERROR");
                });
                socketSerVER.on('makread', function(data) {
                    if (data.nametecnico) {
                        $('.users li[data-id="' + data.id + '"]').attr('data-tecnico', data.tecnico).attr('data-estado', "0");
                        $('.users li[data-id="' + data.id + '"] .operadorasignado').text(data.nametecnico);
                        if ($('.users .active-user[data-id="' + data.id + '"]').length) {
                            $('.messageasigned').show();
                            $('.messageasigned .nameoperador').text(data.nametecnico);
                        }
                    }
                    $('.users li[data-id="' + data.id + '"] .conter-chat-user').text('');
                });
                socketSerVER.on('ackmessage', function(data) {
                    $("span." + data.id.id).removeClass('check-w-0');
                    $("span." + data.id.id).removeClass('check-w-1');
                    $("span." + data.id.id).removeClass('check-w-2');
                    $("span." + data.id.id).addClass('check-w-3');
                });
                $(document).on("click", ".itemoperador", function() {
                    $('.listaoperadores .itemoperador.active').removeClass('active');
                    $('.listaoperadores a[data-asignado="' + $(this).data('asignado') + '"]').addClass('active');
                    $.post("{{route('crm.whatsapp')}}", {
                        id: $('.person.active-user').data('id'),
                        action:"changeTecnico",
                        _token,
                        tecnico: $(this).data('asignado')
                    });
                    socketSerVER.emit('changeoperador', {
                        tecnico: $(this).data('asignado'),
                        id: $('.person.active-user').data('id'),
                        name: $(this).text(),
                        cliente: $('li.person.active-user .name').text()
                    });
                });
                socketSerVER.on('typing', function(data) {
                    if (data.changename) {
                        $('li.person[data-id="' + data.id + '"] .name').text(data.nombre);
                        return;
                    }
                    $('#showtyping,li.person[data-id="' + data.id + '"] .typing').css('display', 'none');
                    if (data.type == "record") {
                        $('li.person[data-id="' + data.id + '"] .nametyping').text(data.name);
                        $('li.person[data-id="' + data.id + '"] .typing:not(li.active-user[data-id="' + data.id + '"] .typing)').css('display', 'block');
                        if ($('li.active-user[data-id="' + data.id + '"]').length && data.tecnico !== "79") {
                            $('#showtyping').css('display', 'block');
                        }
                        return;
                    }
                    if (data.type == "stop") {
                        return;
                    }
                    $('li.person[data-id="' + data.id + '"] .nametyping').text(data.name);
                    $('li.person[data-id="' + data.id + '"] .typing:not(li.active-user[data-id="' + data.id + '"] .typing)').css('display', 'block');
                    clearTimeout(timertyping);
                    if ($('li.active-user[data-id="' + data.id + '"]').length && data.tecnico !== "79") {
                        $('#showtyping').css('display', 'block');
                    }
                    $('#showtyping .typingoperador').text(data.name);
                    timertyping = setTimeout(function() {
                        $('li.person[data-id="' + data.id + '"] .typing').css('display', 'none');
                        if ($('li.active-user[data-id="' + data.id + '"]').length && data.tecnico !== "79") {
                            $('#showtyping').css('display', 'none');
                        }
                    }, 3000);
                });
                socketSerVER.on('newmessagewatme', function(datos) {
                   
                    if(datos.to.includes("@g.us")){
                        return;
                    }
                    if (!$('.users li[data-id="' + datos.to + '"]').length) {
                        if(!datos?.picurl){
                            datos.picurl = "https://ramenparados.com/wp-content/uploads/2019/03/no-avatar-png-8.png";
                        }
                        nombre = datos.to.replace("@c.us","");
                        if(datos?.contact){
                            if(datos.contact?.name){
                                nombre = datos.contact.name;
                            }
                        }
                        let newcontacto = `
                            <li class="person" data-id="`+datos.to+`" data-tecnico="0" data-estado="0" data-time="`+datos.timestamp+`" > `+` 
                                <div class="user">
                                    <img src="${datos.picurl}">
                                </div>` + `
                                <p class="name-time">` + ` 
                                    <span class="name"> ` + nombre + `
                                    </span>` + ` 
                                    <span class="lastmessage"><b>Tú: </b>` + datos.body + `</span>`+`
                                    <span class="operadorasignado"> No asignado </span>` + `
                                    <span class="time">hace un momento </span>` + ` 
                                </p>` + ` 
                                <span class ="conter-chat-user"> 1 </span>
                            </li>`
                        $('.users').prepend(newcontacto);
                        actulizartime();
                    }

                    if (datos.type == 'e2e_notification' || datos.type == 'notification_template' || datos.from == 'status@broadcast') {
                        return;
                    }
                    //$('.users li[data-id="'+datos.to+'"]').attr('data-time',datos.timestamp).attr('data-estado',"0").prependTo(".users");
                    $('.users li[data-id="' + datos.to + '"]').attr('data-time', datos.timestamp).attr('data-estado', "0").prependTo(".users");
                    if (datos.type == 'chat') {
                        $('.users li[data-id="' + datos.to + '"] .lastmessage').html('<b>Tú: </b> '+datos.body);
                    } else {
                        $('.users li[data-id="' + datos.to + '"] .lastmessage').html('<b>Tú: </b> '+typechats[datos.type]);
                        }
                        $('.users li[data-id="' + datos.to + '"]').attr('data-type', datos.type);
                    if ($('.person.active-user').data('id') == datos.to || $('.person.active-user').data('id') == datos.from) {
                       
                        if(datos.hasMedia){
                            
                            datos.type == "ptt"?datos.type = "audio":datos.type = datos.type;
                            datos.type == "sticker"?datos.type = "image":datos.type = datos.type;
                            switch(datos.type){
                                case "document":
                                    data =`
                                    <li class="chat-right">
                                        <div class="chat-text">
                                            <div >
                                                <div id='${datos.id.id}'>
                                                    <i onclick="verContenido('document','${datos.id.id}','${_token}','${datos._data.mimetype}')" class="fas fa-file-archive fa-5x" style="margin-left: 27px;"></i>
                                                </div>
                                                <small style='margin-left: 31px;'>Click 👆👆</small>
                                            </div>
                                            ${datos.body}
                                            <div class="chat-hour" data-time="${datos.timestamp}">
                                                <span class="timeelapsed">hace 18 horas</span>
                                                <span class="${datos.id.id} d-none check-w-0">
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                    `; 
                                    $(".chatContainerScroll").append(data);
                                    $('.chatContainerScroll').animate({
                                        scrollTop: $('.chatContainerScroll').prop("scrollHeight")
                                    });
                                    break;
                                case "audio":
                                    data =`
                                    <li class="chat-right">
                                        <div class="chat-text">
                                            <div >
                                                <div id='${datos.id.id}'>
                                                    <i onclick="verContenido('audio','${datos.id.id}','${_token}','${datos._data.mimetype}')" class="fas fa-microphone fa-5x" style="margin-left: 27px;"></i>
                                                </div>
                                                <small style='margin-left: 31px;'>Click 👆👆</small>
                                            </div>
                                            ${datos.body}
                                            <div class="chat-hour" data-time="${datos.timestamp}">
                                                <span class="timeelapsed">hace 18 horas</span>
                                                <span class="${datos.id.id} d-none check-w-0">
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                    `; 
                                    $(".chatContainerScroll").append(data);
                                    $('.chatContainerScroll').animate({
                                        scrollTop: $('.chatContainerScroll').prop("scrollHeight")
                                    });
                                    break;
                                case "image":
                                    data =`
                                    <li class="chat-right">
                                        <div class="chat-text">
                                            <div >
                                                <div id='${datos.id.id}'>
                                                    <i onclick="verContenido('imagen','${datos.id.id}','${_token}','${datos._data.mimetype}')" class="fas fa-image fa-5x" style="margin-left: 27px;"></i>
                                                </div>
                                                <small style='margin-left: 31px;'>Click 👆👆</small>
                                            </div>
                                            ${datos.body}
                                            <div class="chat-hour" data-time="${datos.timestamp}">
                                                <span class="timeelapsed">hace 18 horas</span>
                                                <span class="${datos.id.id} d-none check-w-0">
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                    `; 
                                    $(".chatContainerScroll").append(data);
                                    $('.chatContainerScroll').animate({
                                        scrollTop: $('.chatContainerScroll').prop("scrollHeight")
                                    });
                                    break;
                                case "video":
                                    data =`
                                    <li class="chat-right">
                                        <div class="chat-text">
                                            <div >
                                                <div id='${datos.id.id}'>
                                                    <i onclick="verContenido('video','${datos.id.id}','${_token}','${datos._data.mimetype}')" class="fas fa-video fa-5x" style="margin-left: 27px;"></i>
                                                </div>
                                                <small style='margin-left: 31px;'>Click 👆👆</small>
                                            </div>
                                            ${datos.body}
                                            <div class="chat-hour" data-time="${datos.timestamp}">
                                                <span class="timeelapsed">hace 18 horas</span>
                                                <span class="${datos.id.id} d-none check-w-0">
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                    `; 
                                    $(".chatContainerScroll").append(data);
                                    $('.chatContainerScroll').animate({
                                        scrollTop: $('.chatContainerScroll').prop("scrollHeight")
                                    });
                                    break;
                                default:
                                    
                                    break;
                            }
                        }else{

                            if(datos.type == "location"){
                                data =`
                                <li class="chat-right">
                                    <div class="chat-text">
                                        <div >
                                            <div id='${datos.id.id}'>
                                                <a target='_blank' href="https://www.google.com/maps?q=${datos.location.latitude},${datos.location.longitude}">
                                                    <i class="fas fa-map fa-5x" style="margin-left: 27px;"></i>
                                                </a>
                                            </div>
                                            <small style='margin-left: 31px;'>Click 👆👆</small>
                                        </div>
                                        <div class="chat-hour" data-time="${datos.timestamp}">
                                            <span class="timeelapsed">hace 18 horas</span>
                                            <span class="${datos.id.id} d-none check-w-0">
                                            </span>
                                        </div>
                                    </div>
                                </li>
                                `; 
                                $(".chatContainerScroll").append(data);
                                $('.chatContainerScroll').animate({
                                    scrollTop: $('.chatContainerScroll').prop("scrollHeight")
                                });
                            }else{
                                let data =`
                                <li class="chat-right">
                                    <div class="chat-text">
                                        ${datos.body}
                                        <div class="chat-hour" data-time="${datos.timestamp}">
                                            <span class="timeelapsed">hace 18 horas</span>
                                            <span class="${datos.id.id} d-none check-w-0">
                                            </span>
                                        </div>
                                    </div>
                                </li>
                                `; 
                                $(".chatContainerScroll").append(data);
                                $('.chatContainerScroll').animate({
                                    scrollTop: $('.chatContainerScroll').prop("scrollHeight")
                                });
                            }
                        }
                        /*
                        
                        */
                        
                    } else {
                        var idconterchat;
                        if ($('.users li[data-id="' + datos.from + '"] .conter-chat-user').is(':empty')) {
                            idconterchat = 0;
                        } else {
                            idconterchat = $('.users li[data-id="' + datos.from + '"] .conter-chat-user').text();
                        }
                        $('.users li[data-id="' + datos.from + '"] .conter-chat-user').text(parseInt(idconterchat) + 1);
                    }
                    actulizartime();
                    unreadwhat();
                    contermischats();
                    if (datos.fromMe) {
                        if (datos.type == 'chat') {
                            $('.users li[data-id="' + datos.to + '"] .lastmessage').html('<b>Tú</b> '+datos.body);
                        } else {
                            $('.users li[data-id="' + datos.to + '"] .lastmessage').html('<b>Tú</b> '+typechats[datos.type]);
                        }
                    }
                });
                socketSerVER.on('newmessagewat', function(datos) {
    
                    if(datos.from.includes("@g.us")){
                        return;
                    }
                    
                    if (datos.type == 'e2e_notification' || datos.type == 'notification_template' || datos.from == 'status@broadcast') {
                        return;
                    }
                    if (!$('.users li[data-id="' + datos.from + '"]').length) {
                        if(!datos?.picurl){
                            datos.picurl = "https://ramenparados.com/wp-content/uploads/2019/03/no-avatar-png-8.png";
                        }
                        nombre = datos.from.replace("@c.us","");
                        if(datos?.contact){
                            if(datos.contact?.name){
                                nombre = datos.contact.name;
                            }
                        }
                        let newcontacto = `
                            <li class="person" data-id="`+datos.from+`" data-tecnico="0" data-estado="0" data-time="`+datos.timestamp+`" > `+` 
                                <div class="user">
                                    <img src="${datos.picurl}">
                                </div>` + `
                                <p class="name-time">` + ` 
                                    <span class="name"> ` + nombre + `
                                    </span>` + ` 
                                    <span class="lastmessage">` + datos.body + `</span>`+`
                                    <span class="operadorasignado"> No asignado </span>` + `
                                    <span class="time">hace un momento </span>` + ` 
                                </p>` + ` 
                                <span class ="conter-chat-user"> 1 </span>
                            </li>`
                        $('.users').prepend(newcontacto);
                        actulizartime();
                    }
                    //$('.users li[data-id="'+datos.to+'"]').attr('data-time',datos.timestamp).attr('data-estado',"0").prependTo(".users");
                    $('.users li[data-id="' + datos.from + '"]').attr('data-time', datos.timestamp).attr('data-estado', "0").prependTo(".users");
                    if (datos.type == 'chat') {
                        $('.users li[data-id="' + datos.from + '"] .lastmessage').text(datos.body);
                    } else {
                        $('.users li[data-id="' + datos.from + '"] .lastmessage').html(typechats[datos.type]);
                        }
                        $('.users li[data-id="' + datos.from + '"]').attr('data-type', datos.type);
                    if ($('.person.active-user').data('id') == datos.to || $('.person.active-user').data('id') == datos.from) {
                        if(datos.hasMedia){         
                            datos.type == "ptt"?datos.type = "audio":datos.type = datos.type;
                            datos.type == "sticker"?datos.type = "image":datos.type = datos.type;
                            switch(datos.type){
                                case "document":
                                    data =`
                                    <li class="chat-left">
                                        <div class="chat-text">
                                            <div >
                                                <div id='${datos.id.id}'>
                                                    <i onclick="verContenido('document','${datos.id.id}','${_token}','${datos._data.mimetype}')" class="fas fa-file-archive fa-5x" style="margin-left: 27px;"></i>
                                                </div>
                                                <small style='margin-left: 31px;'>Click 👆👆</small>
                                            </div>
                                            ${datos.body}
                                            <div class="chat-hour" data-time="${datos.timestamp}">
                                                <span class="timeelapsed">hace 18 horas</span>
                                                <span class="${datos.id.id} d-none check-w-0">
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                    `; 
                                    $(".chatContainerScroll").append(data);
                                    $('.chatContainerScroll').animate({
                                        scrollTop: $('.chatContainerScroll').prop("scrollHeight")
                                    });
                                    break;
                                case "audio":
                                    data =`
                                    <li class="chat-left">
                                        <div class="chat-text">
                                            <div >
                                                <div id='${datos.id.id}'>
                                                    <i onclick="verContenido('audio','${datos.id.id}','${_token}','${datos._data.mimetype}')" class="fas fa-microphone fa-5x" style="margin-left: 27px;"></i>
                                                </div>
                                                <small style='margin-left: 31px;'>Click 👆👆</small>
                                            </div>
                                            ${datos.body}
                                            <div class="chat-hour" data-time="${datos.timestamp}">
                                                <span class="timeelapsed">hace 18 horas</span>
                                                <span class="${datos.id.id} d-none check-w-0">
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                    `; 
                                    $(".chatContainerScroll").append(data);
                                    $('.chatContainerScroll').animate({
                                        scrollTop: $('.chatContainerScroll').prop("scrollHeight")
                                    });
                                    break;
                                case "image":
                                    data =`
                                    <li class="chat-left">
                                        <div class="chat-text">
                                            <div >
                                                <div id='${datos.id.id}'>
                                                    <i onclick="verContenido('imagen','${datos.id.id}','${_token}','${datos._data.mimetype}')" class="fas fa-image fa-5x" style="margin-left: 27px;"></i>
                                                </div>
                                                <small style='margin-left: 31px;'>Click 👆👆</small>
                                            </div>
                                            ${datos.body}
                                            <div class="chat-hour" data-time="${datos.timestamp}">
                                                <span class="timeelapsed">hace 18 horas</span>
                                                <span class="${datos.id.id} d-none check-w-0">
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                    `; 
                                    $(".chatContainerScroll").append(data);
                                    $('.chatContainerScroll').animate({
                                        scrollTop: $('.chatContainerScroll').prop("scrollHeight")
                                    });
                                    break;
                                case "video":
                                    data =`
                                    <li class="chat-left">
                                        <div class="chat-text">
                                            <div >
                                                <div id='${datos.id.id}'>
                                                    <i onclick="verContenido('video','${datos.id.id}','${_token}','${datos._data.mimetype}')" class="fas fa-video fa-5x" style="margin-left: 27px;"></i>
                                                </div>
                                                <small style='margin-left: 31px;'>Click 👆👆</small>
                                            </div>
                                            ${datos.body}
                                            <div class="chat-hour" data-time="${datos.timestamp}">
                                                <span class="timeelapsed">hace 18 horas</span>
                                                <span class="${datos.id.id} d-none check-w-0">
                                                </span>
                                            </div>
                                        </div>
                                    </li>
                                    `; 
                                    $(".chatContainerScroll").append(data);
                                    $('.chatContainerScroll').animate({
                                        scrollTop: $('.chatContainerScroll').prop("scrollHeight")
                                    });
                                    break;
                                default:
                                    break;
                            }
                        }else{
                            if(datos.type == "location"){
                                data =`
                                <li class="chat-left">
                                    <div class="chat-text">
                                        <div >
                                            <div id='${datos.id.id}'>
                                                <a target='_blank' href="https://www.google.com/maps?q=${datos.location.latitude},${datos.location.longitude}">
                                                    <i class="fas fa-map fa-5x" style="margin-left: 27px;"></i>
                                                </a>
                                            </div>
                                            <small style='margin-left: 31px;'>Click 👆👆</small>
                                        </div>
                                        <div class="chat-hour" data-time="${datos.timestamp}">
                                            <span class="timeelapsed">hace 18 horas</span>
                                            <span class="${datos.id.id} d-none check-w-0">
                                            </span>
                                        </div>
                                    </div>
                                </li>
                                `; 
                                $(".chatContainerScroll").append(data);
                                $('.chatContainerScroll').animate({
                                    scrollTop: $('.chatContainerScroll').prop("scrollHeight")
                                });
                            }else{
                                let data =`
                                <li class="chat-left">
                                    <div class="chat-text">
                                        ${datos.body}
                                        <div class="chat-hour" data-time="${datos.timestamp}">
                                            <span class="timeelapsed">hace 18 horas</span>
                                            <span class="${datos.id.id} d-none check-w-0">
                                            </span>
                                        </div>
                                    </div>
                                </li>
                                `; 
                                $(".chatContainerScroll").append(data);
                                $('.chatContainerScroll').animate({
                                    scrollTop: $('.chatContainerScroll').prop("scrollHeight")
                                });
                            }
                        }
                        /*
                        
                        */
                        
                    } else {
                        var idconterchat;
                        if ($('.users li[data-id="' + datos.from + '"] .conter-chat-user').is(':empty')) {
                            idconterchat = 0;
                        } else {
                            idconterchat = $('.users li[data-id="' + datos.from + '"] .conter-chat-user').text();
                        }
                        $('.users li[data-id="' + datos.from + '"] .conter-chat-user').text(parseInt(idconterchat) + 1);
                    }
                    actulizartime();
                    unreadwhat();
                    contermischats();
                    if (datos.fromMe) {
                        if (datos.type == 'chat') {
                            $('.users li[data-id="' + datos.to + '"] .lastmessage').text(datos.body);
                        } else {
                            $('.users li[data-id="' + datos.to + '"] .lastmessage').html(typechats[datos.type]);
                        }
                    }
                });
                socketSerVER.on('info', function(data) {
                    if(typeof data == "string"){
                        data = JSON.parse(data);
                    }
                    data = `
                        <h1>Información del Usuario</h1>
                        <div>Web.V: ${data.web}</div>
                        <div>Name user connected: ${data.client.pushname}</div>
                        <div>Number user connected: ${data.client.wid.user}</div>
                    `;
                    $("#infoModal>.modal-dialog>.modal-content>.modal-body").html(data);
                    $("#infoModal").modal("show");
                });
                socketSerVER.on('changeoperador', function(data) {
                    $('.users li[data-id="' + data.id + '"]').attr('data-tecnico', data.tecnico);
                    if (data.tecnico > 0) {
                        if ($('.active-user[data-id="' + data.id + '"]').length) {
                            $('.messageasigned').show();
                        }
                        
                        $('.nameoperador').text(data.name);
                        $('.person[data-id="' + data.id + '"] .operadorasignado').text('Asignado a ' + data.name);
                    } else {
                        if ($('.active-user[data-id="' + data.id + '"]').length) {
                            $('.messageasigned').hide();
                        }
                        $('.nameoperador').text(data.name);
                        $('.person[data-id="' + data.id + '"] .operadorasignado').text('Asignado a ' + data.name);
                    }
                    setTimeout(() => {
                    contermischats();
                    }, 300);
                });
                socketSerVER.on('closechat', function(id) {
                    $('.users li[data-id="' + id + '"]').attr("data-estado", "1");
                    $('.users li[data-id="' + id + '"]').appendTo('.users');
                    $('.closechatw.ti-close.m-l-10').hide();
                    contermischats();
                    actulizartime();
                });
                setInterval(() => {
                    actulizartime();
                }, 40000);
            })
        @endif
    @endif
    addAutomaticIP();
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    function reloadButton(){
        $("#contenedor_carga").css("visibility","visible").css("opacity","1").prepend("<div style=\"text-align: center;margin-top: 50px;font-size: larger;font-weight: 900;\">Reiniciando la instancia, por favor espere.</div>");
        try{
            $.post("{{route('crm.whatsapp')}}",{action:"reloadInstancia",_token})
            .then((data)=>{
                if(typeof data == "string"){
                    data = JSON.parse(data);
                }
                $("#contenedor_carga").css("visibility","hidden").css("opacity","0").html(`<img id="carga" src="{{asset('images/gif-tuerca.gif')}}">`);
                swal({
                        title: data.message,
                        type: data.salida,
                        showCancelButton: false,
                        showConfirmButton: true,
                        cancelButtonColor: '#00ce68',
                        cancelButtonText: 'Aceptar',
                    });
                if(data.salida == "success"){
                    setTimeout(() => {
                        location.reload();
                    }, 1000);
                }
            })
        }catch(err){
            $("#contenedor_carga").css("visibility","hidden").css("opacity","0").html(`<img id="carga" src="{{asset('images/gif-tuerca.gif')}}">`);
            swal({
                    title: "Ocurrio un error reiniciando la instancia, comunicate con tu proveedor.",
                    type: "error",
                    showCancelButton: false,
                    showConfirmButton: true,
                    cancelButtonColor: '#00ce68',
                    cancelButtonText: 'Aceptar',
                });
        }
    }
    function createInstancia(){
        var addr = $("#ipAddr").val();
        $("#contenedor_carga").css("visibility","visible").css("opacity","1").prepend("<div style=\"text-align: center;margin-top: 50px;font-size: larger;font-weight: 900;\">Creando la instancia, por favor espere.</div>");
        try{
            $.post("{{route('crm.whatsapp')}}",{action:"create",_token,addr})
            .then((data)=>{
                
                if(typeof data == "string"){
                    data = JSON.parse(data);
                }
                $("#contenedor_carga").css("visibility","hidden").css("opacity","0").html(`<img id="carga" src="{{asset('images/gif-tuerca.gif')}}">`);
                swal({
                        title: data.message,
                        type: data.salida,
                        showCancelButton: false,
                        showConfirmButton: true,
                        cancelButtonColor: '#00ce68',
                        cancelButtonText: 'Aceptar',
                    });
                if(data.salida == "success"){
                    location.reload();
                }
            })
        }catch(err){
            $("#contenedor_carga").css("visibility","hidden").css("opacity","0").html(`<img id="carga" src="{{asset('images/gif-tuerca.gif')}}">`);
            swal({
                    title: "Ocurrio un error creando la instancia, comunicate con tu proveedor.",
                    type: "error",
                    showCancelButton: false,
                    showConfirmButton: true,
                    cancelButtonColor: '#00ce68',
                    cancelButtonText: 'Aceptar',
                });
        }
        

    }
    function verContenido(type,id,_token,mimetype){
        if(type == "imagen" || type == "video" ){
            try {
                $("#contenedor_carga").css("visibility","visible").css("opacity","1").prepend("<div style=\"text-align: center;margin-top: 50px;font-size: larger;font-weight: 900;\">Cargando multimedia, por favor espere.</div>");
                $.post("{{route('crm.whatsapp')}}",{action:"getMedia",id,_token,mimetype})
                .then((data)=>{
                    $("#contenedor_carga").css("visibility","hidden").css("opacity","0").html(`<img id="carga" src="{{asset('images/gif-tuerca.gif')}}">`);
                    if(typeof data == "string"){
                        data = JSON.parse(data);
                    }
                    if(data.salida == "success"){
                        let imagen;
                        if(type == "imagen"){
                            imagen = `<img src="${data.src}" class="img-fluid" alt="Imagen Modal">`;
                        }else{
                            imagen = `
                            <video width="auto" height="240" controls="" style="border-radius: 10px;max-width: 100%;" preload="none">
                            <source src="${data.src}" type="video/mp4">
                            </video>`;
                        }
                        let modal = `
                            <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-body">
                                            ${imagen}
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $("#modaltmp").html(modal);
                        $('#myModal').modal('show');
                        return;
                    }else{
                        swal({
                            title: "no fue posible obtener el multimedia",
                            type: "error",
                            showCancelButton: false,
                            showConfirmButton: true,
                            cancelButtonColor: '#00ce68',
                            cancelButtonText: 'Aceptar',
                        }).then((result) => {
                            
                        });
                        return;
                    }
                    
                    
                })
            } catch (error) {
                swal({
                    title: "no fue posible obtener el multimedia",
                    type: "error",
                    showCancelButton: false,
                    showConfirmButton: true,
                    cancelButtonColor: '#00ce68',
                    cancelButtonText: 'Aceptar',
                }).then((result) => {
                    
                });
                return;
            }
        }
        if(type == "audio"){
            try {
                $("#contenedor_carga").css("visibility","visible").css("opacity","1").prepend("<div style=\"text-align: center;margin-top: 50px;font-size: larger;font-weight: 900;\">Cargando multimedia, por favor espere.</div>");
                $.post("{{route('crm.whatsapp')}}",{action:"getMedia",id,_token,mimetype})
                .then((data)=>{
                    $("#contenedor_carga").css("visibility","hidden").css("opacity","0").html(`<img id="carga" src="{{asset('images/gif-tuerca.gif')}}">`);
                    if(typeof data == "string"){
                        data = JSON.parse(data);
                    }
                    if(data.salida == "success"){
                        let imagen = `<audio src="${data.src}" controls></audio>`;
                        
                        let modal = `
                            <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-body">
                                            ${imagen}
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                        $("#modaltmp").html(modal);
                        $('#myModal').modal('show');
                        return;
                    }else{
                        swal({
                            title: "no fue posible obtener el multimedia",
                            type: "error",
                            showCancelButton: false,
                            showConfirmButton: true,
                            cancelButtonColor: '#00ce68',
                            cancelButtonText: 'Aceptar',
                        }).then((result) => {
                            
                        });
                        return;
                    }
                    
                    
                })
            } catch (error) {
                swal({
                    title: "no fue posible obtener el multimedia",
                    type: "error",
                    showCancelButton: false,
                    showConfirmButton: true,
                    cancelButtonColor: '#00ce68',
                    cancelButtonText: 'Aceptar',
                }).then((result) => {
                    
                });
                return;
            }
        }
        if(type == "document"){
            try {
                $("#contenedor_carga").css("visibility","visible").css("opacity","1").prepend("<div style=\"text-align: center;margin-top: 50px;font-size: larger;font-weight: 900;\">Cargando multimedia, por favor espere.</div>");
                $.post("{{route('crm.whatsapp')}}",{action:"getMedia",id,_token,mimetype})
                .then((data)=>{
                    $("#contenedor_carga").css("visibility","hidden").css("opacity","0").html(`<img id="carga" src="{{asset('images/gif-tuerca.gif')}}">`);
                    if(typeof data == "string"){
                        data = JSON.parse(data);
                    }
                    if(data.salida == "success"){
                        const link = document.createElement('a');
                        link.href = data.src;
                        link.download = 'archivo.pdf';
                        link.target = '_blank';
                        link.click();
                        return;
                    }else{
                        swal({
                            title: "no fue posible obtener el multimedia",
                            type: "error",
                            showCancelButton: false,
                            showConfirmButton: true,
                            cancelButtonColor: '#00ce68',
                            cancelButtonText: 'Aceptar',
                        }).then((result) => {
                            
                        });
                        return;
                    }
                    
                    
                })
            } catch (error) {
                swal({
                    title: "no fue posible obtener el multimedia",
                    type: "error",
                    showCancelButton: false,
                    showConfirmButton: true,
                    cancelButtonColor: '#00ce68',
                    cancelButtonText: 'Aceptar',
                }).then((result) => {
                    
                });
                return;
            }
        }
        
    }
    function showInfo(){
        socketSerVER.emit("info", {});
    }
    function addAutomaticIP(){
        let direc = window.location.href;
        let complete = direc.toString().split("://")[1].split("/")[0];
        complete = direc.toString().split("://")[0]+"://"+complete;
        $("#ipAddr").val(complete);

    }
    const miModal = document.getElementById('myModal');
    /*
    miModal.addEventListener('hidden.bs.modal', function () {
        // Limpiar el contenido del modal al cerrarse
        const modalBody = miModal.querySelector('.modal-body');
        modalBody.innerHTML = '';
    });
    */
</script>
@endsection

