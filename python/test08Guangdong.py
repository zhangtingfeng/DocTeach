#!/usr/bin/python3
# coding=utf-8

from asyncio import sleep
import time
from selenium import webdriver
from selenium.webdriver.common.by import By
import re
import random
import logging
import traceback
from savePic import savePic
from SavemysqlAndQurey import getMaxCountNumFromContent, saveContent
import copy


def runMain():
    # 引入日志

    sleep(2)
    getContent()


def getContent():
    time.sleep(random.randint(5, 6))
    # 创建浏览器对象
    Browerdriver = webdriver.Edge()
    Browerdriver.get("http://edu.gd.gov.cn/jyzxnew/gdjyxw/index.html")
    # 打印页面标题“百度一下你就知道”
    print(Browerdriver.title)

    # 打开数据库连接
    logging.basicConfig(filename='log_record.txt', level=logging.DEBUG, filemode='w',
                        format='【%(asctime)s】 【%(levelname)s】 >>>  %(message)s', datefmt='%Y-%m-%d %H:%M')

    crulink = 0

    # elementid = Browerdriver.find_element(By.ID, '277770')
    elementul =Browerdriver.find_element(By.XPATH, '//div[@class="newsList right"]')  # element01Content=Browerdriver.find_element(By.XPATH,'//div[@class="moe-detail-box"]')
    links = elementul.find_elements(By.CSS_SELECTOR, 'a')
    handle_main = Browerdriver.current_window_handle
    alllinkscount = len(links)

    for link in links:
        time.sleep(random.randint(1, 2))  # 暂停5秒输出下一指令

        try:
            crulink = crulink + 1
            link.click()

            handle_all = Browerdriver.window_handles  # 只有2个窗口时
            for h in handle_all:
                if h != handle_main:
                    handle_new = h
            Browerdriver.switch_to.window(handle_new)

            print(Browerdriver.title)

            time.sleep(random.randint(3, 5))  # 暂停5秒输出下一指令
            Out_Comfrom = "广东教育"
            CurAticle = Browerdriver.find_element(By.XPATH, '//div[@class="main"]')

            Out_titleRarticle1 = CurAticle.find_element(By.XPATH, '//div[@class="con"]')
            Out_titleRarticle2 = Out_titleRarticle1.find_element(By.XPATH, '//h3')
            Out_title=Out_titleRarticle2.text



            element02Contentucapcontent = CurAticle.find_element(By.XPATH, '//div[@class="article"]')
            md5sourceContent = element02Contentucapcontent.get_attribute('innerHTML')
            time.sleep(random.randint(1, 2))  # 暂停5秒输出下一指令
            img_elements = element02Contentucapcontent.find_elements(by=By.TAG_NAME, value='img')
            maxCount = getMaxCountNumFromContent() + 1
            savePic(img_elements, "Pic19Guangdong", maxCount, Browerdriver)
            Out_Content = element02Contentucapcontent.get_attribute('innerHTML')
            saveContent(Out_title, Out_Content, Out_Comfrom, md5sourceContent, 19)
            time.sleep(random.randint(5, 20))  # 暂停5秒输出下一指令
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
            time.sleep(random.randint(1, 6))  # 暂停5秒输出下一指令
            # 退出try语句块总会执行的程序


    # //element = Browerdriver.find_element(By.ID, 'page')
    # //NextPage = Browerdriver.find_elements(By.XPATH, '//li[@class="m_page_a m_page_btn"]')
    # NextPage[1].click();
    # sleep(2)

    print(Browerdriver.current_url)
    # 关闭当前页面，如果只有一个页面，会关闭浏览器
    Browerdriver.close()
    # 关闭浏览器
    Browerdriver.quit()

runMain()
