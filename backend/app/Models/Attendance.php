<?php

namespace App\Models;

class Attendance extends Model {
    // تعريف اسم الكوليكشن صراحة في قاعدة البيانات لتجنب أي خطأ برمي في السيرفر
    protected static $collectionName = 'attendances';
    protected static $collection = 'attendances';
}