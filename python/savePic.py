import requests
import time
import configparser
import os
from selenium.webdriver.common.action_chains import ActionChains
from selenium.webdriver.common.keys import Keys
import re
import base64
from io import BytesIO
from PIL import Image
from selenium.webdriver.common.by import By

#https://pythonjishu.com/othjwsbryowo/
def addAttribute(driver, elementobj, attributeName, value):
    '''
    封装向页面标签添加新属性的方法
    调用JS给页面标签添加新属性，arguments[0]~arguments[2]分别
    会用后面的element，attributeName和value参数进行替换
    添加新属性的JS代码语法为：element.attributeName=value
    比如input.name='test'
    '''
    driver.execute_script("arguments[0].%s=arguments[1]" % attributeName, elementobj, value)


def setAttribute(driver, elementobj, attributeName, value):
    '''
    封装设置页面对象的属性值的方法
    调用JS代码修改页面元素的属性值，arguments[0]~arguments[1]分别
    会用后面的element，attributeName和value参数进行替换
    '''
    driver.execute_script("arguments[0].setAttribute(arguments[1],arguments[2])", elementobj, attributeName, value)


def getAttribute(elementobj, attributeName):
    # 封装获取页面对象的属性值方法
    return elementobj.get_attribute(attributeName)


def removeAttribute(driver, elementobj, attributeName):
    '''
    封装删除页面属性的方法
    调用JS代码删除页面元素的指定的属性，arguments[0]~arguments[1]分别
    会用后面的element，attributeName参数进行替换
    '''
    driver.execute_script("arguments[0].removeAttribute(arguments[1])",
                          elementobj, attributeName)

def base64_to_image(base64_str):
    base64_data = re.sub('^data:image/.+;base64,', '', base64_str)
    byte_data = base64.b64decode(base64_data)
    image_data = BytesIO(byte_data)
    img = Image.open(image_data)
    return img


js = "let c = document.createElement('canvas');let ctx = c.getContext('2d');" \
     "let img = document.getElementsByTagName('img')[0]; /*找到图片*/ " \
     "c.height=img.naturalHeight;c.width=img.naturalWidth;" \
     "ctx.drawImage(img, 0, 0,img.naturalWidth, img.naturalHeight);" \
     "let base64String = c.toDataURL();return base64String;"


def se_down(file_path, picture_url,Browerdriver):
    # 这里是调用入口
    Browerdriver.get(picture_url)
    base64_str = Browerdriver.execute_script(js)
    img = base64_to_image(base64_str)
    img = img.convert('RGB')
    img.save(file_path)
    Browerdriver.back()  # 回到空白页面

def savePic(img_elements,SaveCatgory,numName,Browerdriver):
    # 遍历抓到的所有webElement
    # 记录爬取当前的所有url
    picpathSaveCatgory = '/{}/'.format(SaveCatgory)

    config = configparser.ConfigParser()
    config.read('config.ini')
    picPath = config.get('Server', 'picPath')
    picurlPath = config.get('Server', 'picurlPath')


    diskPath = picPath
    img_url_dic = []
    count = 0
    for img_element in img_elements:
        # 获取每个标签元素内部的url所在连接
        img_url = img_element.get_attribute('src')
        # Browerdriver.execute_script("arguments[0].setAttribute('src', '11111111')", img_element)
        if isinstance(img_url, str):
            # 过滤掉无效的url
            if len(img_url) <= 200:
                # 将无效goole图标筛去
                # 每次爬取当前窗口，或许会重复，因此进行去重
                if img_url not in img_url_dic:
                    try:
                        img_url_dic.append(img_url)
                        # 下载并保存图片到当前目录下
                        curpicpath = picpathSaveCatgory + str(numName)+"_"+str(count)+ ".jpg"

                        curdiskfilename = diskPath + curpicpath
                        folder_path = os.path.dirname(curdiskfilename)  # 获取文件夹路径
                        os.makedirs(folder_path, exist_ok=True)  # 创建文件夹（如果不存在）
                        headers = {
                            "User-Agent": "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36 Edg/115.0.1901.200"
                        }
                        pic = requests.get(img_url, headers,timeout=10)
                        fp = open(curdiskfilename, 'wb')
                        fp.write(pic.content)
                        fp.close()

                        #response = requests.get('https://www.vmgirls.com/9384.html', headers=headers)
                        #print(response.request.headers)

                        #Browerdriver.get(img_url)
                        #Browerdriver.get_screenshot_as_file(curdiskfilename)

                       # se_down(curdiskfilename, img_url, Browerdriver)
                        time.sleep(1)
                        #action = ActionChains(Browerdriver).move_to_element(img_element)  # 移动到该元素
                        #action.context_click(img_element).perform()  # 右键点击该元素
                        #action.send_keys(Keys.ARROW_DOWN).perform()  # 点击键盘向下箭头
                        #action.send_keys("v").perform()  # 键盘输入V保存图
                        #action.perform()  # 执行保存

                        #r = requests.get(img_url,headers)
                        #with open(curdiskfilename, 'wb') as f:
                        #    f.write(r.content)
                        #f.close()
                        #*/
                        count += 1

                        setAttribute(Browerdriver, img_element, 'src', picurlPath+curpicpath)
                        setAttribute(Browerdriver, img_element, 'style.height', "auto !important")
                        if (SaveCatgory=="Pic106Suzhou"):
                            removeAttribute(Browerdriver,img_element.find_element(By.XPATH,".."),"href")
                        #setAttribute(Browerdriver, img_element.parent, 'h', picurlPath + curpicpath)
                        #Browerdriver.execute_script("arguments[0].setAttribute('src', '"+picurlPath+curpicpath+"')", img_element)
                        #Browerdriver.execute_script(f"arguments[0].src = '{picurlPath+curpicpath}'",
                        #                             img_element)
                        #Browerdriver.execute_script("arguments[0].style.height=arguments[1]", img_element, "auto !important")

                        print('this is ' + str(count) + 'st img')
                        # 防止反爬机制
                        time.sleep(2.5)
                    except Exception as e:
                        print(e)