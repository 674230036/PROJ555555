CREATE DATABASE IF NOT EXISTS food_db
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE food_db;

-- ===========================
-- ตารางอาหาร (Food)
-- ===========================
CREATE TABLE foods (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name_th VARCHAR(255) NOT NULL,
    category VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===========================
-- ตารางวัตถุดิบ (Recipe)
-- ===========================
CREATE TABLE recipes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    food_id INT NOT NULL,
    recipe_name VARCHAR(255) NOT NULL,
    quantity FLOAT NOT NULL,
    unit_name VARCHAR(50) NOT NULL,

    CONSTRAINT fk_food
    FOREIGN KEY (food_id)
    REFERENCES foods(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ===========================
-- ข้อมูลตัวอย่าง
-- ===========================

INSERT INTO foods(name_th,category)
VALUES
('ผัดไทยกุ้งสด','อาหารคาว'),
('ข้าวผัดหมู','อาหารคาว'),
('ชาไทย','เครื่องดื่ม');

INSERT INTO recipes(food_id,recipe_name,quantity,unit_name)
VALUES
(1,'เส้นจันท์',150,'กรัม'),
(1,'กุ้งสด',5,'ตัว'),
(1,'ไข่ไก่',1,'ฟอง'),
(1,'ถั่วงอก',50,'กรัม'),

(2,'ข้าวสวย',1,'จาน'),
(2,'หมู',100,'กรัม'),
(2,'ไข่ไก่',1,'ฟอง'),

(3,'ชาไทย',2,'ช้อน'),
(3,'นมข้นหวาน',30,'มิลลิลิตร'),
(3,'นมสด',50,'มิลลิลิตร');