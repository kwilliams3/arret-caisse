<?php
use Core\Model\App;
use Core\Model\Session;
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Arrêts Caisses - Connexion</title>
    <!-- FONT AWESOME 4.5.0 LOCAL -->
    <link rel="stylesheet" href="Public/font-awesome/4.5.0/css/font-awesome.min.css">
    
    <!-- POLICE POPPINS LOCALE -->
    <style>
        /* Poppins - Police locale */
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 300;
            src: url('Public/fonts/poppins-v20-latin-300.woff2') format('woff2'),
                 url('Public/fonts/poppins-v20-latin-300.woff') format('woff');
        }
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 400;
            src: url('Public/fonts/poppins-v20-latin-regular.woff2') format('woff2'),
                 url('Public/fonts/poppins-v20-latin-regular.woff') format('woff');
        }
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 500;
            src: url('Public/fonts/poppins-v20-latin-500.woff2') format('woff2'),
                 url('Public/fonts/poppins-v20-latin-500.woff') format('woff');
        }
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 600;
            src: url('Public/fonts/poppins-v20-latin-600.woff2') format('woff2'),
                 url('Public/fonts/poppins-v20-latin-600.woff') format('woff');
        }
        @font-face {
            font-family: 'Poppins';
            font-style: normal;
            font-weight: 700;
            src: url('Public/fonts/poppins-v20-latin-700.woff2') format('woff2'),
                 url('Public/fonts/poppins-v20-latin-700.woff') format('woff');
        }
        
        *{margin:0;padding:0;box-sizing:border-box;}
        body{font-family:'Poppins',sans-serif;overflow:hidden;background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);}
        .container{display:flex;min-height:100vh;}
        
        /* Partie gauche */
        .presentation-section{flex:1;background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);display:flex;align-items:center;justify-content:center;padding:3rem;position:relative;overflow:hidden;}
        .presentation-section::before{content:'';position:absolute;width:150%;height:150%;background:radial-gradient(circle,rgba(255,255,255,0.1)0%,transparent 70%);animation:rotate 20s linear infinite;}
        @keyframes rotate{from{transform:rotate(0deg);}to{transform:rotate(360deg);}}
        .presentation-content{position:relative;z-index:1;color:white;max-width:600px;}
        .presentation-content h2{font-size:4rem;font-weight:700;margin-bottom:2rem;line-height:1.2;}
        .presentation-content h2 span{display:block;font-size:2.8rem;font-weight:300;opacity:0.9;}
        
        .feature-list{margin-top:3.5rem;}
        .feature-item{display:flex;align-items:center;gap:2rem;margin-bottom:2.5rem;padding:1.2rem;background:rgba(255,255,255,0.1);border-radius:20px;backdrop-filter:blur(10px);transition:transform 0.3s ease;}
        .feature-item:hover{transform:translateX(10px);background:rgba(255,255,255,0.15);}
        .feature-icon{width:70px;height:70px;background:rgba(255,255,255,0.2);border-radius:18px;display:flex;align-items:center;justify-content:center;font-size:2.2rem;}
        .feature-text h3{font-size:1.8rem;font-weight:600;margin-bottom:0.5rem;}
        .feature-text p{font-size:1.2rem;opacity:0.9;}
        
        /* Partie droite */
        .login-section{flex:1;background:white;display:flex;align-items:center;justify-content:center;padding:2rem;position:relative;overflow:hidden;}
        .login-section::before{content:'';position:absolute;top:-50%;left:-50%;width:100%;height:100%;background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);opacity:0.05;border-radius:50%;}
        .login-container{width:100%;max-width:500px;position:relative;z-index:1;margin-top:0px;display:flex;flex-direction:column;align-items:center;}
        
        .logo-area{text-align:center;margin-bottom:1rem;width:100%;}
        .logo-icon{width:100px;height:100px;background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);border-radius:28px;display:flex;align-items:center;justify-content:center;margin:0 auto 1rem;box-shadow:0 15px 40px rgba(30,60,114,0.4);}
        .logo-icon i{font-size:50px;color:white;}
        .login-container h1{font-size:3rem;font-weight:700;margin-bottom:0.3rem;text-align:center;}
        .login-container h1 .title-main{background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;}
        .login-container h1 .title-sub{color:#2a5298;}
        .company{color:#999;font-size:1.1rem;font-weight:300;text-align:center;margin-bottom:1.5rem;}
        
        .welcome-text{text-align:center;margin-bottom:1.5rem;width:100%;}
        .welcome-text h2{font-size:2.2rem;color:#333;margin-bottom:0.5rem;}
        .welcome-text p{color:#777;font-size:1.2rem;}
        
        /* Formulaire centré */
        .form-Connection{width:100%;display:flex;flex-direction:column;align-items:center;}
        fieldset{width:100%;border:none;display:flex;flex-direction:column;align-items:center;}
        
        .input-group{margin-bottom:1.2rem;position:relative;width:100%;max-width:400px;}
        .input-group label{display:block;margin-bottom:0.4rem;color:#555;font-weight:500;font-size:1.1rem;text-align:left;}
        .input-wrapper{position:relative;width:100%;}
        .input-wrapper i{position:absolute;left:18px;top:50%;transform:translateY(-50%);color:#999;transition:all 0.3s ease;font-size:1.3rem;}
        .input-wrapper input{width:100%;padding:15px 18px 15px 55px;border:2px solid #e1e1e1;border-radius:14px;font-size:1.2rem;font-family:'Poppins',sans-serif;transition:all 0.3s ease;background:#f8f9fa;}
        .input-wrapper input:focus{outline:none;border-color:#2a5298;background:white;box-shadow:0 8px 25px rgba(42,82,152,0.15);}
        .input-wrapper input:focus+i{color:#2a5298;}
        .input-wrapper input::placeholder{color:#aaa;font-weight:300;font-size:1.1rem;}
        
        .btn-login{width:100%;max-width:400px;padding:15px;background:linear-gradient(135deg,#1e3c72 0%,#2a5298 100%);border:none;border-radius:14px;color:white;font-size:1.3rem;font-weight:600;font-family:'Poppins',sans-serif;cursor:pointer;transition:all 0.3s ease;box-shadow:0 8px 25px rgba(30,60,114,0.4);display:flex;align-items:center;justify-content:center;gap:10px;margin-top:0.8rem;}
        .btn-login:hover{transform:translateY(-2px);box-shadow:0 12px 35px rgba(30,60,114,0.5);}
        .btn-login i{font-size:1.3rem;}
        
        .message-area{margin-bottom:1rem;padding:12px;border-radius:12px;display:none;font-size:1.1rem;text-align:center;animation:fadeIn 0.3s ease;width:100%;max-width:400px;}
        @keyframes fadeIn{from{opacity:0;transform:translateY(-10px);}to{opacity:1;transform:translateY(0);}}
        .message-area.error{display:block;background:#fee;color:#c33;border:1px solid #fcc;}
        .message-area.success{display:block;background:#e8f5e9;color:#2e7d32;border:1px solid #a5d6a7;}
        .message-area.info{display:block;background:#e3f2fd;color:#1976d2;border:1px solid #bbdefb;}
        .message-area.warning{display:block;background:#fff3e0;color:#f57c00;border:1px solid #ffe0b2;}
        
        .loader{text-align:center;margin-top:1rem;width:100%;}
        .loader i{font-size:2rem;color:#2a5298;}
        .footer-text{margin-top:1.5rem;text-align:center;font-size:1.1rem;color:#999;width:100%;}
        .footer-text a{color:#2a5298;text-decoration:none;font-weight:500;font-size:1.1rem;}
        .footer-text a:hover{text-decoration:underline;}
        .hidden{display:none!important;}
        
        /* Modal */
        .modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:1000;align-items:center;justify-content:center;}
        .modal.active{display:flex;}
        .modal-content{background:white;border-radius:25px;width:90%;max-width:500px;box-shadow:0 30px 80px rgba(0,0,0,0.3);animation:slideUp 0.3s ease;}
        @keyframes slideUp{from{transform:translateY(50px);opacity:0;}to{transform:translateY(0);opacity:1;}}
        .modal-header{padding:1.5rem;border-bottom:1px solid #eee;display:flex;align-items:center;justify-content:space-between;}
        .modal-header h3{color:#333;font-weight:600;font-size:1.5rem;}
        .modal-header h3 i{font-size:1.5rem;margin-right:10px;color:#2a5298;}
        .modal-header .close{background:none;border:none;font-size:2rem;cursor:pointer;color:#999;transition:color 0.3s ease;}
        .modal-header .close:hover{color:#333;}
        .modal-body{padding:1.5rem;display:flex;flex-direction:column;align-items:center;}
        .modal-body form{width:100%;display:flex;flex-direction:column;align-items:center;}
        .modal-body .input-group{max-width:100%;}
        .modal-footer{padding:1.2rem 1.5rem;border-top:1px solid #eee;text-align:right;}
        .btn-secondary{padding:10px 22px;background:#f1f1f1;border:none;border-radius:10px;color:#666;cursor:pointer;font-weight:500;font-size:1rem;transition:all 0.3s ease;}
        .btn-secondary:hover{background:#e1e1e1;}
        .btn-success{background:linear-gradient(135deg,#27ae60,#2a5298);color:white;}
        
        @media (max-width:768px){.container{flex-direction:column;}.presentation-section{display:none;}.login-section{padding:1.5rem;}.login-container{max-width:100%;}.logo-icon{width:80px;height:80px;}.logo-icon i{font-size:40px;}.login-container h1{font-size:2.2rem;}.input-group{max-width:100%;}}
        
        /* FontAwesome Fixes pour FA4 */
        .fa {
            font-family: FontAwesome !important;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="presentation-section">
            <div class="presentation-content">
                <h2>Gérez vos arrêts de caisse<span>en toute simplicité</span></h2>
                <div class="feature-list">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fa fa-eye"></i>
                        </div>
                        <div class="feature-text">
                            <h3>Suivi en temps réel</h3>
                            <p>Visualisez et suivez tous vos arrêts de caisse instantanément</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fa fa-lock"></i>
                        </div>
                        <div class="feature-text">
                            <h3>Sécurité maximale</h3>
                            <p>Vos données sont protégées avec un chiffrement de bout en bout</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <i class="fa fa-hourglass-start"></i>
                        </div>
                        <div class="feature-text">
                            <h3>Gain de temps</h3>
                            <p>Automatisez vos processus et gagnez en efficacité</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="login-section">
            <div class="login-container">
                <div class="logo-area">
                    <div class="logo-icon">
                        <i class="fa fa-credit-card"></i>
                    </div>
                    <h1><span class="title-main">Arrêts</span> <span class="title-sub">Caisses</span></h1>
                    <div class="company"><i class="fa fa-copyright"></i> SOREPCO Entreprise</div>
                </div>
                <div class="welcome-text">
                    <h2>Connexion</h2>
                    <p>Connectez-vous à votre espace de travail</p>
                </div>
                <div class="message-area" id="messageArea"><p class="message"></p></div>
                <form action="<?= App::url('ajax.home.loginUser')?>" method="post" id="form-LoginUserConnect" class="form-Connection">
                    <fieldset>
                        <div class="input-group">
                            <label for="login">Identifiant</label>
                            <div class="input-wrapper">
                                <i class="fa fa-user"></i>
                                <input type="text" id="login" name="login" placeholder="Entrez votre identifiant" required>
                            </div>
                        </div>
                        <div class="input-group">
                            <label for="password">Mot de passe</label>
                            <div class="input-wrapper">
                                <i class="fa fa-lock"></i>
                                <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
                            </div>
                        </div>
                        <button type="submit" class="btn-login">
                            <i class="fa fa-key"></i>Se connecter
                        </button>
                        <div class="loader hidden">
                            <i class="fa fa-spinner fa-spin"></i>
                        </div>
                    </fieldset>
                </form>
                <div class="footer-text">En vous connectant, vous acceptez nos <a href="#">conditions d'utilisation</a></div>
            </div>
        </div>
    </div>

    <div id="modalResetPass" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fa fa-key"></i>Réinitialisation</h3>
                <button class="close" onclick="closeResetModal()">&times;</button>
            </div>
            <div class="modal-body">
                <form action="<?= App::url('ajax.home.resetPass') ?>" method="POST" id="form-resetPass">
                    <input type="hidden" class="idClient1" id="idClient1" />
                    <div class="message-area" id="messageResetArea"><p class="messageReset"></p></div>
                    <div class="input-group">
                        <label for="passwordReset">Nouveau mot de passe</label>
                        <div class="input-wrapper">
                            <i class="fa fa-lock"></i>
                            <input type="password" id="passwordReset" name="passwordReset" placeholder="Entrer le mot de passe" required>
                        </div>
                    </div>
                    <div class="input-group">
                        <label for="confirmReset">Confirmation</label>
                        <div class="input-wrapper">
                            <i class="fa fa-lock"></i>
                            <input type="password" id="confirmReset" name="confirmReset" placeholder="Confirmer le mot de passe" required>
                        </div>
                    </div>
                    <button type="submit" class="btn-login btn-success" style="margin-top:0.5rem;">
                        <i class="fa fa-check"></i>Valider
                    </button>
                    <div class="loader hidden">
                        <i class="fa fa-spinner fa-spin"></i>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closeResetModal()">
                    <i class="fa fa-times"></i>Fermer
                </button>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('form-LoginUserConnect').addEventListener('submit', function(e){
            e.preventDefault();
            const form=this, msg=document.getElementById('messageArea'), loader=document.querySelector('.loader'), btn=document.querySelector('.btn-login');
            msg.classList.remove('error','success','info','warning'); msg.style.display='none';
            loader.classList.remove('hidden'); btn.disabled=true;
            msg.classList.add('info'); msg.querySelector('.message').innerHTML='<i class="fa fa-spinner fa-spin"></i> Connexion en cours...'; msg.style.display='block';
            
            fetch(form.action,{method:'POST',body:new FormData(form),headers:{'X-Requested-With':'XMLHttpRequest'}})
            .then(r=>r.ok?r.json():Promise.reject())
            .then(d=>{
                loader.classList.add('hidden'); btn.disabled=false;
                if(d.success){
                    msg.classList.remove('info'); msg.classList.add('success');
                    msg.querySelector('.message').innerHTML='<i class="fa fa-check-circle"></i> Connexion réussie ! Redirection...';
                    setTimeout(()=>window.location.href=d.redirect||'dashboard.php',1500);
                }else{
                    msg.classList.remove('info'); msg.classList.add('error');
                    msg.querySelector('.message').innerHTML='<i class="fa fa-exclamation-circle"></i> '+(d.message||'Identifiant ou mot de passe incorrect');
                }
            })
            .catch(()=>{
                loader.classList.add('hidden'); btn.disabled=false;
                msg.classList.remove('info'); msg.classList.add('warning');
                msg.querySelector('.message').innerHTML='<i class="fa fa-exclamation-triangle"></i> Problème de connexion...';
            });
        });

        document.getElementById('form-resetPass').addEventListener('submit', function(e){
            e.preventDefault();
            const form=this, msg=document.getElementById('messageResetArea'), loader=this.querySelector('.loader'), btn=this.querySelector('.btn-login');
            const pwd=document.getElementById('passwordReset').value, confirm=document.getElementById('confirmReset').value;
            msg.classList.remove('error','success','info','warning'); msg.style.display='none';
            if(pwd!==confirm){msg.classList.add('error'); msg.querySelector('.messageReset').innerHTML='<i class="fa fa-exclamation-circle"></i> Les mots de passe ne correspondent pas'; msg.style.display='block'; return;}
            if(pwd.length<6){msg.classList.add('error'); msg.querySelector('.messageReset').innerHTML='<i class="fa fa-exclamation-circle"></i> 6 caractères minimum'; msg.style.display='block'; return;}
            loader.classList.remove('hidden'); btn.disabled=true;
            msg.classList.add('info'); msg.querySelector('.messageReset').innerHTML='<i class="fa fa-spinner fa-spin"></i> Traitement...'; msg.style.display='block';
            
            fetch(form.action,{method:'POST',body:new FormData(form),headers:{'X-Requested-With':'XMLHttpRequest'}})
            .then(r=>r.json())
            .then(d=>{
                loader.classList.add('hidden'); btn.disabled=false;
                if(d.success){
                    msg.classList.remove('info'); msg.classList.add('success');
                    msg.querySelector('.messageReset').innerHTML='<i class="fa fa-check-circle"></i> Réussite !';
                    setTimeout(closeResetModal,1500);
                }else{
                    msg.classList.remove('info'); msg.classList.add('error');
                    msg.querySelector('.messageReset').innerHTML='<i class="fa fa-exclamation-circle"></i> '+(d.message||'Erreur');
                }
            })
            .catch(()=>{
                loader.classList.add('hidden'); btn.disabled=false;
                msg.classList.remove('info'); msg.classList.add('error');
                msg.querySelector('.messageReset').innerHTML='<i class="fa fa-exclamation-circle"></i> Erreur serveur';
            });
        });

        function closeResetModal(){document.getElementById('modalResetPass').classList.remove('active'); document.getElementById('form-resetPass').reset();}
        window.onclick=e=>{if(e.target===document.getElementById('modalResetPass'))closeResetModal();}
    </script>
</body>
</html>