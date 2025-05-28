<?php

namespace App\Http\Controllers;

use App\Http\Resources\Constant\GradeResource;
use App\Models\EBook;
use App\Models\EbookReading;
use App\Models\Grade;
use App\Models\Library;
use App\Models\LibraryBranch;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ReadingPerformanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (!$user || !isset($user->roles[0])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $role = strtolower($user->roles[0]->name);
        $loanCounts = collect();
        $ebookCounts = collect();

        switch ($role) {
            case 'superadmin':
                $loanCounts = $this->getBookLoanCounts(); 
                $ebookCounts = $this->getEBookReadCounts();
                break;

            case 'admin'||'librarian':
                // Assuming the user is assigned to a branch via branch_id on the users table
                $branchId = $user->library_branch_id ?? null;

                if ($branchId) {
                    $loanCounts = $this->getBookLoanCounts(branchIds: [$branchId]);
                    $ebookCounts = $this->getEBookReadCounts(branchIds: [$branchId]);
                }
                break;

            // case 'librarian':
            //     $libraryId = Library::where('librarian_id', $user->id)->value('id');
            //     if ($libraryId) {
            //         $loanCounts = $this->getBookLoanCounts(libraryIds: [$libraryId]);
            //         $ebookCounts = $this->getEBookReadCounts(libraryIds: [$libraryId]);
            //     }
            //     break;

            case 'teacher':
                $ebookCounts = $this->getEBookReadCounts(teacherId: $user->id);
                break;

            default:
                return response()->json(['message' => 'Unauthorized'], 403);
        }
        // Combine and sum all read counts (loan + ebook)
        $combinedCounts = $loanCounts->mergeRecursive($ebookCounts)->map(function ($value) {
            return is_array($value) ? array_sum($value) : $value;
        });

        // Load grades with sections, students, users, and their ebookReadings
        $grades = Grade::with([
            'sections.students.user.ebookReadings'
        ])->get();

        // Prepare response with grade and section counts
        $result = $grades->map(function ($grade) use ($combinedCounts) {
            $gradeSectionCount = 0;
            $sections = $grade->sections->map(function ($section) use ($combinedCounts, &$gradeSectionCount) {
            // Sum ebook reading count from eager-loaded data
            $ebookReadCount = $section->students->sum(function ($student) {
                return optional($student->user)->ebookReadings->sum('read_count') ?? 0;
            });

            // Get loan+ebook count from pre-grouped data
            $loanCount = $combinedCounts[$section->id] ?? 0;

            $sectionReadCount = $ebookReadCount + $loanCount;
            $gradeSectionCount += $sectionReadCount;

            return [
                'id' => $section->id,
                'name' => $section->name,
                'read_count' => $sectionReadCount,
            ];
            });

            return [
            'id' => $grade->id,
            'name' => $grade->name,
            'read_count' => $gradeSectionCount,
            'sections' => $sections,
            ];
        });

        return response()->json(['data' => $result]);
    }

        // Attach read count to each section
        //
        // $data = $grades->map(function ($grade) {
        //     $readCount = $grade->users->flatMap(function ($user) {
        //         return $user->ebookReadings;
        //     })->sum('read_count');
        //     return [
        //         'grade_id' => $grade->id,
        //         'grade_name' => $grade->name,
        //         'read_count' => $readCount,
        //     ];
        // });

        // return



    private function getBookLoanCounts($branchIds = null, $libraryIds = null)
{
    $query = Loan::with('user')->whereNotNull('user_id');

    // if ($libraryIds) {
    //     // $query->whereIn('id', $libraryIds);
    //     $query->whereHas('user', fn($q) => $q->whereIn('library_branch_id', $libraryIds)); // ✅ corrected here

    // }

    if ($branchIds) {
        $query->whereHas('user', fn($q) => $q->whereIn('library_branch_id', $branchIds)); // ✅ corrected here
    }

    $loans = $query->get();

    return $loans->groupBy(function ($loan) {
        $sectionId = $loan->user?->section_id;
        if (!$sectionId) {
            Log::warning("Loan missing section: user_id={$loan->user?->id}");
        }
        return $sectionId;
    })->map(fn($group) => $group->count())
      ->filter(fn($count, $key) => $key !== null);
}

    private function getEBookReadCounts($teacherId = null, $branchIds = null, $libraryIds = null)
{
    $query = EbookReading::with('user');

    if ($teacherId) {
        $ebookIds = EBook::where('user_id', $teacherId)->pluck('id');
        $query->whereIn('ebook_id', $ebookIds);
    }

    if ($libraryIds) {
        $query->whereHas('user', fn($q) => $q->whereIn('library_id', $libraryIds));
    }

    if ($branchIds) {
        $query->whereHas('user', fn($q) => $q->whereIn('library_branch_id', $branchIds)); // ✅ corrected here
    }

    $readings = $query->get();

    return $readings->groupBy(function ($reading) {
        $sectionId = $reading->user?->section_id;
        if (!$sectionId) {
            Log::warning("EbookReading missing section: user_id={$reading->user?->id}");
        }
        return $sectionId;
    })->map(fn($group) => $group->sum('read_count'))
      ->filter(fn($count, $key) => $key !== null);
}

}
