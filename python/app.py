from flask import Flask
import mysql.connector

app = Flask(__name__)

db = mysql.connector.connect(
    host="mysql",
    user="root",
    password="root",
    database="student_management"
)

@app.route('/')
def home():

    cursor = db.cursor()

    cursor.execute("SELECT COUNT(*) FROM students")

    count = cursor.fetchone()[0]

    return {
        "total_students": count
    }

app.run(host='0.0.0.0', port=5000)