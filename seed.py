import mysql.connector
from datetime import datetime
from faker import Faker
import random

# ===== กำหนดจำนวนข้อมูลที่ต้องการ =====
NUM_POSTS = 5    # จำนวน posts ที่ต้องการเพิ่ม
NUM_USERS = 3    # จำนวน users ที่ต้องการเพิ่ม

# ===== การเชื่อมต่อฐานข้อมูล =====
db = mysql.connector.connect(
    host="192.168.2.12",
    user="root",          # แก้เป็น user ของคุณ
    password="rootpassword",  # แก้เป็นรหัสผ่านของคุณ
    database="php_blog"
)
cursor = db.cursor()

fake = Faker("th_TH")  # ใช้ข้อมูลภาษาไทย

# ========================
# สร้างข้อมูล posts แบบสุ่ม
# ========================
posts = []
for _ in range(NUM_POSTS):
    title = fake.sentence(nb_words=5)
    content = fake.paragraph(nb_sentences=5)
    image = f"uploads/{fake.file_name(extension='png')}"
    category = random.choice(["ข่าวสารมหาวิทยาลัย", "กิจกรรมกีฬา", "กิจกรรมวิชาการ"])
    tag = random.choice(["งานปฐมนิเทศ", "สัปดาห์วิชาการ", "กิจกรรมกีฬา"])
    author = random.choice(["admin", "staff", "editor"])
    posts.append((title, content, image, category, tag, author, datetime.now()))

cursor.executemany(
    """INSERT INTO posts (posts_title, posts_content, posts_image, category_name, tag_name, author, created_at)
       VALUES (%s, %s, %s, %s, %s, %s, %s)""",
    posts
)

# ========================
# สร้างข้อมูล users แบบสุ่ม
# ========================
users = []
for _ in range(NUM_USERS):
    name = fake.name()
    username = fake.user_name()
    email = fake.email()
    student_id = None
    major = None
    password = "21232f297a57a5a743894a0e4a801fc3"  # md5(admin) ตัวอย่าง
    role = random.choice([0, 1, 3])
    users.append((name, username, email, student_id, major, password, role, datetime.now()))

cursor.executemany(
    """INSERT INTO user_info (name, username, email, student_id, major, password, role, created_at)
       VALUES (%s, %s, %s, %s, %s, %s, %s, %s)""",
    users
)

# ========================
# บันทึกและปิดการเชื่อมต่อ
# ========================
db.commit()
cursor.close()
db.close()

print(f"✅ เพิ่มข้อมูลสุ่ม: posts {NUM_POSTS} รายการ และ users {NUM_USERS} รายการเรียบร้อย")
