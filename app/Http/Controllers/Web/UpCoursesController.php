<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class UpCoursesController extends Controller
{
    public function readCourses(){
        $fileContents = Storage::disk('public')->get('file_courses/course_info.json');
        $fileUrl = Storage::disk('public')->url('file_courses/course_info.json');
            
        // dd($fileUrl,$fileContents);
        $data=json_decode($fileContents,true);
        
        foreach($data as $value){
            
        }
        return view('test',compact('fileContents'));
    }
}
