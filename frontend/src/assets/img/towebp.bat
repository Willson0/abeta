@echo off
for %%f in (*.jpg) do (
    D:\programs\libwebp-1.5.0-windows-x64\bin/cwebp "%%f" -o "%%~nf.webp"
)
for %%f in (*.png) do (
    D:\programs\libwebp-1.5.0-windows-x64\bin/cwebp "%%f" -o "%%~nf.webp"
)
pause