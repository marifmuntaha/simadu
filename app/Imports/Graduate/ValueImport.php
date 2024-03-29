<?php

namespace App\Imports\Graduate;

use App\Models\Graduate\Value;
use App\Models\Master\Subject;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class ValueImport implements ToCollection
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $heading = $rows[0];
        $body = json_decode(json_encode($rows), true);
        array_splice($body, 0, 1);
        foreach (Subject::all() as $subject) {
            foreach ($body as $row){
                for ($i=0;$i<count($heading);$i++){
                    if ($subject->subject_code == $heading[$i]){
                        $value = new Value();
                        $value->value_point = $row[$i];
                        $value->value_subject = $subject->subject_id;
                        $value->save();
                        $value->student()->attach($row[0]);
                    }
                }
            }
        }
    }
}
