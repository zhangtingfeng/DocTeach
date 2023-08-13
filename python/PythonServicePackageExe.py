# -*- coding: utf-8 -*-

"""
使用Python写Windows Service服务程序
python PythonServicePackageExe.py  执行打包，添加计划任务即可
pip install pyinstaller
https://cloud.tencent.com/developer/article/2152976?areaSource=102001.14&traceId=ZsXKIK5eZ-l_Ocnq9jQxq
pyinstaller -F -w PythonService.py
test01MoeGovCn
"""

from PyInstaller.__main__ import run

if __name__ == '__main__':

  params = ['test06SuZhou.py', '-F', '-c']

  run(params)