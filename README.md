📄 توثيق مشروع نظام إدارة التعلم (LMS)
1. نظرة عامة على المشروع
مشروع نظام إدارة التعلم هو تطبيق ويب كامل (Full-Stack) مبني باستخدام لغة البرمجة PHP وقاعدة بيانات MySQL. يهدف المشروع إلى توفير بيئة تعليمية متكاملة عبر الإنترنت، مع ثلاث لوحات تحكم مستقلة لكل من: الإداري، المعلم، والطالب. يوفر النظام وظائف أساسية لإدارة المستخدمين، المواد، الواجبات، ودرجات الطلاب.

التقنيات المستخدمة:

الخلفية (Backend): PHP 8+

قاعدة البيانات: MySQL/MariaDB

الواجهة الأمامية (Frontend): HTML5, CSS3, JavaScript

مكتبة الواجهة: Bootstrap 5.3.3

أمن البيانات: Prepared Statements, Password Hashing

2. هيكلية الملفات
يتبع المشروع هيكلاً تنظيمياً واضحاً يفصل بين أدوار المستخدمين المختلفة والملفات الأساسية المشتركة، مما يسهل عملية الصيانة والتطوير.

/LMS-Project
├── /admin                      # لوحة تحكم الأدمن
│   ├── add_course_process.php
│   ├── create.php
│   ├── dashboard.php
│   ├── delete.php
│   ├── delete_course.php
│   ├── edit.php
│   ├── edit_course.php
│   ├── enroll_process.php
│   ├── manage_courses.php
│   ├── manage_enrollments.php
│   └── unenroll.php
│
├── /includes                   # الملفات الأساسية المشتركة
│   ├── admin_navbar.php
│   ├── auth.php
│   ├── database.php
│   ├── student_navbar.php
│   └── teacher_navbar.php
│
├── /student                    # لوحة تحكم الطالب
│   ├── courses.php
│   ├── dashboard.php
│   ├── grades.php
│   └── submit_assignment.php
│
├── /teacher                    # لوحة تحكم المعلم
│   ├── add_assignment_process.php
│   ├── dashboard.php
│   ├── delete_assignment.php
│   ├── edit_assignment.php
│   ├── grade_submission.php
│   ├── grades.php
│   ├── my_courses.php
│   └── view_assignment.php
│
├── /upload                     # مجلد لرفع الملفات
│   ├── /assignments
│   └── /submissions
│
├── create_account.php
├── index.php
├── login.php
├── logout.php
└── setup_admin.php             # ملف لإنشاء حساب الأدمن (للاستخدام لمرة واحدة)
3. قاعدة البيانات
يتم تنظيم بيانات المشروع عبر 5 جداول رئيسية، كل جدول يخدم غرضاً محدداً ومترابطاً مع الجداول الأخرى عبر مفاتيح خارجية (Foreign Keys).

المخطط العلائقي (ERD):

users (1) <---> courses (N) (via teacher_id)

users (1) <---> enrollments (N) (via student_id)

courses (1) <---> enrollments (N) (via course_id)

courses (1) <---> assignments (N) (via course_id)

assignments (1) <---> submissions (N) (via assignment_id)

users (1) <---> submissions (N) (via student_id)

تفاصيل الجداول:

users:

الغرض: تخزين معلومات المستخدمين (الإداريين، المعلمين، الطلاب).

الأعمدة:

id: المعرف الفريد للمستخدم (Primary Key, AUTO_INCREMENT).

name: اسم المستخدم (VARCHAR, UNIQUE).

password: كلمة المرور المشفرة (VARCHAR).

phone: رقم الهاتف (VARCHAR).

role: دور المستخدم (admin, teacher, student).

courses:

الغرض: تخزين معلومات المواد الدراسية.

الأعمدة:

id: المعرف الفريد للمادة (Primary Key, AUTO_INCREMENT).

title: عنوان المادة (VARCHAR).

description: وصف تفصيلي للمادة (TEXT).

teacher_id: المعرف الفريد للمعلم الذي يدرس المادة (Foreign Key to users.id).

enrollments:

الغرض: ربط الطلاب بالمواد التي سجلوا فيها.

الأعمدة:

id: المعرف الفريد للتسجيل (Primary Key, AUTO_INCREMENT).

student_id: المعرف الفريد للطالب (Foreign Key to users.id).

course_id: المعرف الفريد للمادة (Foreign Key to courses.id).

assignments:

الغرض: تخزين معلومات الواجبات المتعلقة بالمواد.

الأعمدة:

id: المعرف الفريد للواجب (Primary Key, AUTO_INCREMENT).

title: عنوان الواجب (VARCHAR).

description: وصف الواجب (TEXT).

course_id: المعرف الفريد للمادة التي يتبعها الواجب (Foreign Key to courses.id).

due_date: تاريخ ووقت التسليم (DATETIME).

file_path: مسار الملف المرفق مع الواجب (VARCHAR).

submissions:

الغرض: تخزين تقديمات الطلاب للواجبات.

الأعمدة:

id: المعرف الفريد للتقديم (Primary Key, AUTO_INCREMENT).

assignment_id: المعرف الفريد للواجب (Foreign Key to assignments.id).

student_id: المعرف الفريد للطالب (Foreign Key to users.id).

file_path: مسار ملف التقديم أو الرابط (VARCHAR).

grade: الدرجة التي حصل عليها الطالب (DECIMAL(5,2)).

4. تحليل الملفات والعمليات
📁 includes - الملفات الأساسية
database.php:

يحتوي على إعدادات الاتصال بقاعدة البيانات (MySQLi) وينشئ كائن الاتصال $conn.

يستخدم die() لإيقاف التنفيذ في حالة فشل الاتصال.

auth.php:    

يبدأ جلسة عمل (Session).

يحتوي على دالتين رئيسيتين:

check_login(): تتحقق مما إذا كان المستخدم قد قام بتسجيل الدخول. إذا لم يكن كذلك، تعيد توجيهه إلى صفحة login.php.

check_role($required_role): تتحقق من أن المستخدم لديه الدور المطلوب. إذا لم يكن الدور صحيحاً، تعرض رسالة خطأ وتوقف التنفيذ.

admin_navbar.php, teacher_navbar.php, student_navbar.php:

ملفات مشتركة تحتوي على شرائط التنقل (Navigation Bars) الخاصة بكل دور.

📁 admin - لوحة تحكم الأدمن
dashboard.php:

الصفحة الرئيسية للأدمن.

تعرض جدولاً بجميع المستخدمين في النظام.

تحتوي على أزرار لـ "إضافة" و "تعديل" و "حذف" المستخدمين.

create.php:

نموذج لإضافة مستخدم جديد.

يعالج طلب POST لإدخال البيانات إلى جدول users.

يستخدم password_hash() لتشفير كلمة المرور.

edit.php:

نموذج لتعديل بيانات مستخدم موجود.

يعالج طلب POST لتحديث البيانات.

يسمح بتحديث كلمة المرور فقط إذا تم إدخال قيمة جديدة.

delete.php:

يعالج عملية حذف المستخدم من جدول users.

manage_courses.php:

يعرض جدولاً بجميع المواد الدراسية.

يحتوي على أزرار لـ "إضافة" و "تعديل" و "حذف" المواد.

يتضمن نافذة منبثقة (Modal) لإضافة مادة جديدة.

add_course_process.php:

يعالج طلب POST لإضافة مادة جديدة إلى جدول courses.

edit_course.php:

نموذج لتعديل بيانات مادة موجودة.

delete_course.php:

يعالج عملية حذف المادة من جدول courses.

manage_enrollments.php:

يعرض جدولاً بتسجيلات الطلاب الحالية.

يحتوي على نموذج لتسجيل طالب في مادة.

enroll_process.php:

يعالج طلب POST لإضافة سجل جديد في جدول enrollments.

يتحقق من عدم وجود تسجيل مسبق قبل الإضافة.

unenroll.php:

يعالج عملية حذف تسجيل طالب من مادة.

📁 teacher - لوحة تحكم المعلم
dashboard.php:

الصفحة الرئيسية للمعلم.

تعرض جدولاً بالمواد المسندة للمعلم الحالي.

my_courses.php:

صفحة إدارة مادة محددة.

تعرض الطلاب المسجلين في المادة وجميع الواجبات الخاصة بها.

تتيح إضافة واجب جديد.

add_assignment_process.php:

يعالج طلب POST لإضافة واجب جديد.

يتضمن وظيفة لرفع ملفات الواجبات إلى مجلد upload/assignments/.

edit_assignment.php:

نموذج لتعديل واجب موجود.

يسمح بتحديث معلومات الواجب أو استبدال الملف المرفق.

delete_assignment.php:

يعالج عملية حذف واجب.

يقوم أولاً بحذف جميع التقديمات (Submissions) المرتبطة بالواجب قبل حذفه.

view_assignment.php:

يعرض تفاصيل واجب محدد.

يسرد جميع تقديمات الطلاب لهذا الواجب مع خيار لتقييمها (إدخال درجة).

grade_submission.php:

يعالج طلب POST لتحديث درجة تقديم طالب في جدول submissions.

يحتوي على تحقق أمني للتأكد من أن المعلم لديه صلاحية تقييم الواجب.

grades.php:

يسمح للمعلم باختيار مادة وعرض جدول يوضح درجات جميع الطلاب في جميع واجبات تلك المادة.

📁 student - لوحة تحكم الطالب
dashboard.php:

الصفحة الرئيسية للطالب.

تعرض جدولاً بجميع المواد التي تم تسجيل الطالب فيها.

courses.php:

صفحة تفاصيل المادة.

تعرض وصف المادة والواجبات المتعلقة بها.

يستطيع الطالب تقديم الواجبات من هذه الصفحة عبر نافذة منبثقة.

submit_assignment.php:

يعالج طلب POST لتقديم الواجب.

يسمح للطالب إما برفع ملف أو إدخال رابط.

يقوم بحذف التقديمات السابقة للطالب لنفس الواجب إذا وجدت.

grades.php:

يعرض جدولاً بجميع الدرجات التي حصل عليها الطالب في مختلف المواد.

📁 الملفات الرئيسية
index.php:

صفحة التوجيه الرئيسية.

إذا كان المستخدم مسجلاً، يعيد توجيهه إلى لوحة التحكم الخاصة بدوره. إذا لم يكن، يعيد توجيهه إلى صفحة تسجيل الدخول.

login.php:

واجهة تسجيل الدخول.

يتحقق من اسم المستخدم وكلمة المرور عبر قاعدة البيانات.

يستخدم password_verify() للتحقق من كلمة المرور المشفرة.

يعيد توجيه المستخدم إلى لوحة التحكم الصحيحة بناءً على دوره.

create_account.php:

صفحة لإنشاء حساب طالب جديد.

يتحقق من أن اسم المستخدم غير موجود بالفعل.

يقوم بتشفير كلمة المرور قبل الحفظ.

logout.php:

يقوم بإنهاء جلسة العمل وإعادة توجيه المستخدم إلى صفحة تسجيل الدخول.

setup_admin.php:

سكربت للاستخدام لمرة واحدة لإنشاء حساب الأدمن الافتراضي.

يحتوي على تحذير هام بضرورة حذفه بعد الاستخدام لأسباب أمنية.
