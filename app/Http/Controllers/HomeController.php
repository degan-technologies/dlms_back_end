<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\EBook;
use App\Models\Student;
use App\Models\EbookType;
use App\Models\Language;
use App\Models\User;
use App\Models\Subject;

class HomeController extends Controller
{
    public function getCounts()
    {
        $totalBooks = Book::count();
        $totalEbooks = EBook::count();
        $totalStudents = Student::count();
        $totalLanguages = Language::count();
        $totalUsers = User::count();
        $totalSubjects = Subject::count();

        // Case-insensitive match for 'video' and 'audio'
        $videoType = EbookType::whereRaw('LOWER(name) = ?', ['video'])->first();
        $audioType = EbookType::whereRaw('LOWER(name) = ?', ['audio'])->first();

        $totalVideos = $videoType ? $videoType->ebooks()->count() : 0;
        $totalAudios = $audioType ? $audioType->ebooks()->count() : 0;

        return response()->json([
            'total_books' => $totalBooks,
            'total_ebooks' => $totalEbooks,
            'total_students' => $totalStudents,
            'total_videos' => $totalVideos,
            'total_audios' => $totalAudios,
            'total_languages' => $totalLanguages,
            'total_users' => $totalUsers,
            'total_subjects' => $totalSubjects,
        ]);
    }
}
