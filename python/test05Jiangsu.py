#!/usr/bin/python3
# coding=utf-8

from asyncio import sleep
import time
from selenium import webdriver
from selenium.webdriver.common.by import By
import re

import logging
import traceback
from savePic import savePic
from SavemysqlAndQurey import  getMaxCountNumFromContent,saveContent

def runMain():
    # 引入日志

    sleep(2)
    getContent()





def getContent():
    # 创建浏览器对象
    Browerdriver = webdriver.Edge()
    Browerdriver.get("test04ZheJiang.py")
    # 打印页面标题“百度一下你就知道”
    print(Browerdriver.title)

    # 打开数据库连接
    logging.basicConfig(filename='log_record.txt', level=logging.DEBUG, filemode='w',
                        format='【%(asctime)s】 【%(levelname)s】 >>>  %(message)s', datefmt='%Y-%m-%d %H:%M')

    crulink = 0
    while True:
        elementid = Browerdriver.find_element(By.ID, '4735063')
        #//elementul =elementid.find_element(By.XPATH, '//ul')  # element01Content=Browerdriver.find_element(By.XPATH,'//div[@class="moe-detail-box"]')
        links = elementid.find_elements(By.CSS_SELECTOR, 'a')
        handle_main = Browerdriver.current_window_handle
        alllinkscount = len(links)
        if crulink > 10:
            break

        for link in links:
            crulink = crulink + 1
            link.click()

            handle_all = Browerdriver.window_handles  # 只有2个窗口时
            for h in handle_all:
                if h != handle_main:
                    handle_new = h
            Browerdriver.switch_to.window(handle_new)

            print(Browerdriver.title)

            try:

                Out_Comfrom = "浙江教育"

                Out_title = Browerdriver.title;
                element02Content = Browerdriver.find_element(By.ID, 'zoom')
                sourceContent = element02Content.get_attribute('innerHTML')
                img_elements = element02Content.find_elements(by=By.TAG_NAME, value='img')
                maxCount=getMaxCountNumFromContent()+1
                savePic(img_elements, "Pic33ZheJiang",maxCount, Browerdriver)
                Out_Content = element02Content.get_attribute('innerHTML')
                saveContent(Out_title, Out_Content, Out_Comfrom,sourceContent,33)

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
                time.sleep(1)  # 暂停5秒输出下一指令
                print("12")
                # 退出try语句块总会执行的程序

        element = Browerdriver.find_element(By.ID, 'page')
        NextPage = Browerdriver.find_elements(By.XPATH, '//li[@class="m_page_a m_page_btn"]')
        NextPage[1].click();
        sleep(2)

    print(Browerdriver.current_url)
    # 关闭当前页面，如果只有一个页面，会关闭浏览器
    Browerdriver.close()
    # 关闭浏览器
    Browerdriver.quit()


runMain()
