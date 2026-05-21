@echo off
setlocal

:: Configurações de Data e Hora para o padrão solicitado
for /f "tokens=2 delims==" %%I in ('wmic os get localdatetime /value') do set datetime=%%I
set YYYY=%datetime:~0,4%
set MM=%datetime:~4,2%
set DD=%datetime:~6,2%
set HH=%datetime:~8,2%
set Min=%datetime:~10,2%

set DATA_HORA=%YYYY%-%MM%-%DD% %HH%:%Min%

:: Tenta pegar a URL do remote
git remote get-url origin >nul 2>&1
if %errorlevel% neq 0 (
    echo.
    echo [!] Este projeto ainda nao esta conectado ao GitHub.
    set /p REPO_URL="[?] Cole aqui a URL do seu repositorio no GitHub (ex: https://github.com/usuario/projeto.git): "
    git remote add origin %REPO_URL%
    git branch -M main
)

echo.
echo [+] Salvando alteracoes...
git add .

set /p MSG="[?] Nome da alteracao (Ex: Pequenos ajustes): "
if "%MSG%"=="" set MSG=Atualizacao Automatica

echo [+] Criando versao: - %MSG% + %DATA_HORA%
git commit -m "- %MSG% + %DATA_HORA%"

echo [+] Enviando para o GitHub...
git push -u origin main

echo.
if %errorlevel% equ 0 (
    echo [OK] Sucesso! Seu projeto ja esta no ar.
) else (
    echo [!] Algo deu errado. Verifique se a URL do GitHub esta correta.
)

pause
