<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Owenoj\LaravelGetId3\GetId3;
use Pion\Laravel\ChunkUpload\Handler\HandlerFactory;
use Pion\Laravel\ChunkUpload\Receiver\FileReceiver;

class FileUploadController extends Controller {

    public function index() {
        $videos = Video::where('section_id','!=',null)->get();
        return view( 'index',compact('videos'));
    }

    public function uploadLargeFiles( Request $request ) {

        $receiver = new FileReceiver( 'file', $request, HandlerFactory::classFromRequest( $request ) );

        if ( !$receiver->isUploaded() ) {
            // file not uploaded
        }

        $fileReceived = $receiver->receive(); // receive file
        $video = new Video();
        if ( $fileReceived->isFinished() ) { // file uploading is complete / all chunks are uploaded

            $track = new GetId3( $fileReceived->getFile() );
            $duration = floor( $track->getPlaytimeSeconds() );

            $file = $fileReceived->getFile(); // get file
            $extension = $file->getClientOriginalExtension();
            // $fileName = str_replace('.'.$extension, '', $file->getClientOriginalName()); //file name without extenstion
            $now = Carbon::now();
            $fileName = uniqid() . '_' . $now . '.' . $extension; // a unique file name
//            $disk = Storage::disk( config( 'filesystems.default' ) );
            $disk = Storage::disk( 'public' );
            $path = $disk->putFileAs( 'course_temp_video', $file, $fileName );
            $video->duration = $duration;
            $video->file = $fileName;
            $video->save();

            // delete chunked file
            unlink( $file->getPathname() );
            return [
                'path'           => asset( 'storage/' . $path ),
                'filename'       => $fileName,
                'last_insert_id' => $video->id,
                'success'        => 'Video Uploaded',
            ];
        }

        // otherwise return percentage informatoin
        $handler = $fileReceived->handler();
        return [
            'done'   => $handler->getPercentageDone(),
            'status' => true,
        ];
    }

    public function uploadUpdate( Request $request ) {
        $lastInsertVideo = Video::where( 'id', $request->lastId )->first();
        $lastInsertVideo->title         = $request->title;
        $lastInsertVideo->section_id    = $request->section_id;
        $lastInsertVideo->video_privacy = $request->video_privacy;

        $storageFolder = $request->course_name;
        if ( !file_exists($storageFolder ) ) {
            mkdir($storageFolder, 0777, true );
        }
        Storage::move( 'course_temp_video/' . $lastInsertVideo->file,$storageFolder.'/'.$lastInsertVideo->file);
        //$videoFile->move( 'storage/' . $folderName, $filePath );
        $lastInsertVideo->save();
        return redirect()->back();

    }
}
