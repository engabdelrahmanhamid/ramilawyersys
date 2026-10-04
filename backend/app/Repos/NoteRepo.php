<?php

namespace App\Repos;

use App\Core\AppResult;
use App\Helpers\ImageHelper;
use App\Models\Note;
use Validator, Auth, Artisan, Hash, File, Crypt;

class NoteRepo
{
    /**
     * @param $id
     * @return AppResult
     */
    public function getNoteById($id)
    {
        $note = Note::findOrfail($id);
        return AppResult::success($note);
    }

    /**
     * @param $payload
     * @return Note
     */
    public function create($payload)
    {
        $note = new Note();
        $note->name = $payload->name;
        $note->date = $payload->date;
        $note->case_id = $payload->case_id;
        $note->file = ImageHelper::getInstance()->saveImage('Note',$payload->file);
        $note->save();
        return $note;
    }

    /**
     * @param $payload
     * @param $note
     * @return mixed
     */
    public function update($payload, $note)
    {
        if(isset($payload->name))
            $note->name = $payload->name;
        if(isset($payload->date))
            $note->date = $payload->date;
        if(isset($payload->case_id))
            $note->case_id = $payload->case_id;
        if($payload->file){
            ImageHelper::getInstance()->deleteFile('Note',$note->file);
            $note->file = ImageHelper::getInstance()->saveImage('Note',$payload->file);
        }
        $note->save();
        return $note;
    }

    /**
     * @param $note
     * @return void
     */
    public function delete($note)
    {
        ImageHelper::getInstance()->deleteFile('Note',$note->file);
        $note->delete();
    }

}
