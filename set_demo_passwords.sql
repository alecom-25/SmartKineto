-- Rulează acest fișier după kim_manager.sql dacă vrei ca butoanele demo din frontend să meargă sigur.
-- Parolele setate sunt:
-- admin@kim.ro       -> admin123
-- alecom@gmail.com   -> alexia
-- isandrei@gmail.com -> 123456

USE kim_manager;

UPDATE users
SET password_hash = '$2y$12$UMBL5Ud2mH8g5mZ0106pB.mebYVV4jqmfEuVAEgnn5dwC0pXuR8zq'
WHERE email = 'admin@kim.ro';

UPDATE users
SET password_hash = '$2y$12$FXcgwQoCUrBNO2Xyonp39eVbxe.QjPEH4Fg3IX93MgRPqVwDg5nuW'
WHERE email = 'alecom@gmail.com';

UPDATE users
SET password_hash = '$2y$12$.7G07KAHjscE93BzbtuOfu3jxvGWVqxufpT702Yj/v9.Azbvvtva.'
WHERE email = 'isandrei@gmail.com';
