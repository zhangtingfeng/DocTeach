import hashlib
import pymysql
import configparser


def getMD5(stringinput):
    md5 = hashlib.md5()
    string = stringinput
    byte_string = string.encode()
    md5.update(byte_string)
    md5_result = md5.hexdigest()
    return md5_result


def getConn():
    config = configparser.ConfigParser()
    config.read('config.ini')
    localhost = config.get('Database', 'host')
    user = config.get('Database', 'user')
    password = config.get('Database', 'password')
    database = config.get('Database', 'database')

    dbconn = pymysql.connect(host=localhost,
                             user=user,
                             password=password,
                             database=database)
    return dbconn



def getMaxCountNumFromContent():
    dbconn=getConn()
    # 获取一个游标对象
    cursor = dbconn.cursor()
    SelctCountsql = "SELECT max(id) as maxid FROM ay_content"
    cursor.execute(SelctCountsql)
    # 获取查询结果
    myresult = cursor.fetchone()[0]
    if (myresult==None):
        myresult=-1
    cursor.close()
    dbconn.close()
    return myresult


def saveContent(Out_title,Out_Content,Out_Comfrom,md5sourceContent,intcity_ID):
    dbconn=getConn()
    # 获取一个游标对象
    cursor = dbconn.cursor()
    # sql语句中，用%s做占位符，参数用一个元组
    insertSql = "insert into ay_content(title,content,author,md5sign) values(%s,%s,%s,%s)"
    insertSqlay_content_sateArea = "insert into ay_content_city(content_ID,city_ID) values(%s,"+str(intcity_ID)+")"

    stringlen = len(Out_Content)
    print(stringlen)
    if stringlen < 65535000000:
        md5Sign = getMD5(Out_title + md5sourceContent + Out_Comfrom)
        # 执行SELECT语句
        SelctCountsql = "SELECT count(1) FROM ay_content WHERE md5sign = '" + md5Sign + "'"
        cursor.execute(SelctCountsql)
        # 获取查询结果
        myresult = cursor.fetchone()[0]
        if myresult == 0:
            # h1 = element.find_elements(By.CSS_SELECTOR,'h1')
            param = (Out_title, Out_Content, Out_Comfrom, md5Sign)
            # 执行数据库插入
            cursor.execute(insertSql, param)
            # 获取自动生成的id
            inserted_id = cursor.lastrowid
            param = (inserted_id)
            cursor.execute(insertSqlay_content_sateArea, param)
            dbconn.commit()

    cursor.close()
    dbconn.close()
