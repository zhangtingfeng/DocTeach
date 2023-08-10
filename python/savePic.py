import requests
import time
import configparser
import os
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

                        r = requests.get(img_url)
                        with open(curdiskfilename, 'wb') as f:
                            f.write(r.content)
                        f.close()
                        count += 1
                        Browerdriver.execute_script(f"arguments[0].src = '{picurlPath+curpicpath}'",
                                                    img_element)

                        print('this is ' + str(count) + 'st img')
                        # 防止反爬机制
                        time.sleep(0.5)
                    except:
                        print('failure')