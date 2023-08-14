#!/usr/bin/python3
# coding=utf-8

from asyncio import sleep
import time
from selenium import webdriver
from selenium.webdriver.common.by import By
import logging
import traceback
from savePic import savePic
from SavemysqlAndQurey import  getMaxCountNumFromContent,saveContent
import random
def runMain():
    # 引入日志

    sleep(2)
    getContent()





def getContent():
    time.sleep(random.randint(5, 10))
    # 创建浏览器对象
    Browerdriver = webdriver.Edge()
    Browerdriver.get("http://jyt.henan.gov.cn/jydt/")
    # 打印页面标题“百度一下你就知道”
    print(Browerdriver.title)

    # 打开数据库连接
    logging.basicConfig(filename='log_record.txt', level=logging.DEBUG, filemode='w',
                        format='【%(asctime)s】 【%(levelname)s】 >>>  %(message)s', datefmt='%Y-%m-%d %H:%M')

    crulink = 0
    while True:
        element = Browerdriver.find_element(By.CLASS_NAME, 'list')
        links = element.find_elements(By.CSS_SELECTOR, 'a')
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

                Out_Comfrom = "河南教育"

                Out_title = Browerdriver.title;
                element02Content = Browerdriver.find_element(By.ID, 'det')
                sourceContent = element02Content.get_attribute('innerHTML')
                img_elements = element02Content.find_elements(by=By.TAG_NAME, value='img')
                maxCount=getMaxCountNumFromContent()+1
                savePic(img_elements, "Pic16Henan",maxCount, Browerdriver)
                Out_Content = element02Content.get_attribute('innerHTML')
                saveContent(Out_title, Out_Content, Out_Comfrom,sourceContent,16)
                time.sleep(random.randint(5, 20))
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
                time.sleep(random.randint(1, 6))
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
