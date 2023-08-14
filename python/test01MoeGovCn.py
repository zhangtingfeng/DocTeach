#!/usr/bin/python3
#coding=utf-8
 
from asyncio import sleep
import time
from selenium import webdriver
from selenium.webdriver.common.by import By
import re
import pymysql
import hashlib
import logging
import traceback

import random

def runMain():
    # 引入日志
   
    
    sleep(2)
    getContent()

   



def getMD5(stringinput):
    md5 = hashlib.md5()
    string = stringinput
    byte_string = string.encode()
    md5.update(byte_string)
    md5_result = md5.hexdigest()
    return md5_result

def getContent():
    time.sleep(random.randint(3,6))
    #创建浏览器对象
    Browerdriver = webdriver.Edge()
    Browerdriver.get("https://hudong.moe.gov.cn/jyb_sy/sy_jyyw/")
    #打印页面标题“百度一下你就知道”
    print(Browerdriver.title)

    # 打开数据库连接
    dbconn = pymysql.connect(host='127.0.0.1',
                        user='demo1',
                        password='Foodztf1#',
                        database='demo1')
    logging.basicConfig(filename='log_record.txt', level=logging.DEBUG, filemode='w', format='【%(asctime)s】 【%(levelname)s】 >>>  %(message)s', datefmt = '%Y-%m-%d %H:%M')

    
    #获取一个游标对象
    cursor=dbconn.cursor()
    #sql语句中，用%s做占位符，参数用一个元组
    insertSql="insert into ay_content(title,content,author,md5sign) values(%s,%s,%s,%s)"
    insertSqlay_content_sateArea="insert into ay_content_city(content_ID,city_ID) values(%s,0)"
    crulink=0
    while True:
        element=Browerdriver.find_element(By.ID, 'list')
        links = element.find_elements(By.CSS_SELECTOR,'a') 
        handle_main = Browerdriver.current_window_handle
        alllinkscount=len(links)
        if crulink>10:
            break    

        for link in links: 
            crulink=crulink+1
            link.click()
                
            handle_all = Browerdriver.window_handles  # 只有2个窗口时
            for h in handle_all:
               if h != handle_main:
                   handle_new = h
            Browerdriver.switch_to.window(handle_new)

            print(Browerdriver.title)
            
            try:    
                #element=driver.find_element(By.CSS_SELECTOR, '.oe-detail-box') 
                element01Content=Browerdriver.find_element(By.XPATH,'//div[@class="moe-detail-box"]')
                # 根据css选择器选择元素
                h1=element01Content.find_element(By.XPATH,'//h1')
                ComeFrom=element01Content.find_element(By.XPATH,'//div[@class="moe-detail-shuxing"]')
                Out_Comfrom=ComeFrom.text;
                Out_Comfrom=re.sub('\s', ' ', Out_Comfrom)
            
                Out_title=h1.text;
                element02Content=element01Content.find_element(By.XPATH,'//div[@class="TRS_Editor"]')
                Out_Content=element02Content.get_attribute('innerHTML')
                stringlen=len(Out_Content)
                print(stringlen)
                if stringlen<65535000000:
                    md5Sign=getMD5(Out_title+Out_Content+Out_Comfrom)
                    # 执行SELECT语句
                    SelctCountsql = "SELECT count(1) FROM ay_content WHERE md5sign = '"+md5Sign+"'"
                    cursor.execute(SelctCountsql)
                    # 获取查询结果
                    myresult = cursor.fetchone()[0]
                    if myresult==0:
                        #h1 = element.find_elements(By.CSS_SELECTOR,'h1') 
                        param=(Out_title,Out_Content,Out_Comfrom,md5Sign)
                        #执行数据库插入
                        cursor.execute(insertSql,param)
                        # 获取自动生成的id
                        inserted_id = cursor.lastrowid
                        param=(inserted_id)
                        cursor.execute(insertSqlay_content_sateArea,param)
                        dbconn.commit()
                time.sleep(random.randint(5, 8))  # 暂停5秒输出下一指令
            except Exception as e:
                logging.error("主程序抛错：")
                logging.error(e)
                logging.error("\n" + traceback.format_exc()) 
                print(traceback.format_exc())   
            except: 
                print("11")    
            finally:
                Browerdriver.close()
                Browerdriver.switch_to.window(handle_main)
                print("12")
               #退出try语句块总会执行的程序 
        

        element=Browerdriver.find_element(By.ID, 'page')
        NextPage=Browerdriver.find_elements(By.XPATH,'//li[@class="m_page_a m_page_btn"]')
        NextPage[1].click();
        sleep(2)
    dbconn.close()
    cursor.close()

    print(Browerdriver.current_url)
    #关闭当前页面，如果只有一个页面，会关闭浏览器
    Browerdriver.close()
    #关闭浏览器
    Browerdriver.quit()

runMain()
