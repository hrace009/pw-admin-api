# pw-admin-api
Api de uma painel de administração do jogo Perfect World

PW-ADMIN API

CONFIGURAÇÕES

As instruções a seguir irão servir para o sistema funcionar no seu diretório local para desenvolvimento

PRE REQUISITOS

XAMPP / PHP ^7 E MYSQL

GIT - VERSIONAMENTO

Instalando
Siga o passo a passo a baixo após a instalação dos itens acima

em "C:\Windows\System32\drivers\etc\"

copie o arquivo "host" para outro local para editá-lo
e adicione a seguinte linha

127.0.0.1 api.pw-admin.com
agora copie ele novamente para  "C:\Windows\System32\drivers\etc\" e substitua o arquivo já existente


isto fará com que o seu computador redirecione o endereço para o seu computador


CLONANDO

Abra a pasta "C:\xampp\htdocs\dashboard\" (ou outra da sua escolha)
e abra o GIT BASH nela e execute o comando abaixo

git clone https://github.com/thalyswolf/pw-admin-api

Em seguida, vá até o diretório pw-admin-api e execute o comando
composer install

Configurando o vhost

abra o arquivo "C:\xampp\apache\conf\extra\httpd-vhosts.conf" em seu editor de texto

e adicione as seguintes linhas no final do arquivo

´´´
<VirtualHost api.pw-admin.com:80>
    ServerAdmin SEU NOME@thalys.local
    DocumentRoot "C:\xammpp7\htdocs\website\pw-admin-api\web" (diretório de preferencia)
    ServerName api.pw-admin.com
</VirtualHost>

´´´
Agora é só reiniciar o apache e deve estar tudo funcionando.
