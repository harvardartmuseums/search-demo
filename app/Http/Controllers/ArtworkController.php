<?php

namespace App\Http\Controllers;

use App\ArticleArtwork;
use App\Artwork;
use App\Helpers\ArtworkHelper;
use App\Services\ArtworkPrevNext;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class ArtworkController extends Controller
{
    public function __construct()
    {
        parent::__construct();

//        $this->forgetBeforeFilter('auth.ham_basic', ['only' => 'getView']);
    }

    public function getView($artworknumber)
    {
        $user = Auth::user();
        $object = null;
        if ((strpos($artworknumber, '.') === false) && (is_numeric($artworknumber))) {
            $object = \HamObject::find($artworknumber);
            $entries = \HamObjectEntries::find($artworknumber);
            if(is_iterable($entries))
                foreach ($entries as &$entry) {
                    if (! empty($entry->publication_id)) {
                        $entry->publication = \HamPublication::find($entry->publication_id);
                    }
                }
            else {
                $entries = [];
            }
        }
        if (empty($object->objectid)) {
            $objects = \HamObject::query(['objectnumber' => $artworknumber])->find();
            if (! empty($objects)) {
                $object = $objects[0];

                return $this->getView($object->objectid);
            }
        }
        if ($object && ! empty($object->title)) {
            $related_objects = \HamObject::relatedto($object->objectid)->sort('objectid')->find();
            $related_tours = Artwork::getToursRelated($object->objectnumber);
            $related_articles = ArticleArtwork::forArtwork($object->objectid)->get();

            if (! empty($object->primaryimageurl)) {
                $meta_image = ArtworkHelper::image_url($object->primaryimageurl, $object->imagepermissionlevel);
            } else {
                $meta_image = '/assets/icons/fb-og-image-400x400.png';
            }

            View::share(
                [
                'meta_description'=> $object->title,
                'meta_title' => 'From the Harvard Art Museums’ collections '.$object->title,
                'meta_image' => $meta_image,
                ]
            );

            if (Request::get('position', null) != null) {
                if (Session::get('input') != null) {
                    $prevNextService = new ArtworkPrevNext(Session::get('input'), Request::get('position'), Session::get('input')['sort']);
                    $previousArtwork = $prevNextService->prev();
                    $nextArtwork = $prevNextService->next();
                    $positionPrevious = $prevNextService->prev_pos();
                    $positionNext = $prevNextService->next_pos();
                    $closeArtwork = $prevNextService->artworkClose();
                
                    return view(
                      'site/artwork/view',
                      compact(
                          'object',
                          'entries',
                          'user',
                          'nextArtwork',
                          'previousArtwork',
                          'related_tours',
                          'related_objects',
                          'positionPrevious',
                          'positionNext',
                          'related_articles',
                          'closeArtwork'
                      )
                  );
                } else {
                    return view(
                        'site/artwork/view',
                        compact('object', 'entries', 'user', 'related_tours', 'related_objects', 'related_articles')
                    );
                }
            } else {
                return view(
                      'site/artwork/view',
                      compact('object', 'entries', 'user', 'related_tours', 'related_objects', 'related_articles')
                  );
            }
        } else {
            abort(404);
        }
    }

    public function getPdfView($artworknumber)
    {
        $user = Auth::user();
        $object = null;
        if ((strpos($artworknumber, '.') === false) && (is_numeric($artworknumber))) {
            $object = \HamObject::find($artworknumber);
        }
        if (empty($object->objectid)) {
            $objects = \HamObject::query(['objectnumber' => $artworknumber])->find();
            if (! empty($objects)) {
                $object = $objects[0];
            }
        }
        if ($object && ! empty($object->title)) {
            $related_objects = \HamObject::relatedto($object->objectid)->sort('objectid')->find();
            $related_tours = Artwork::getToursRelated($object->objectnumber);

            if (! empty($object->primaryimageurl)) {
                $meta_image = ArtworkHelper::image_url($object->primaryimageurl, $object->imagepermissionlevel);
            } else {
                $meta_image = '/assets/icons/fb-og-image-400x400.png';
            }

            View::share(
                [
                'meta_description'=> $object->title,
                'meta_title' => 'From the Harvard Art Museums’ collections '.$object->title,
                'meta_image' => $meta_image,
                ]
            );

            return view('site/artwork/view_pdf', compact(
                'object',
                'user',
                'related_tours',
                'related_objects'
            ));
        } else {
            abort(404);
        }
    }

    /*  public function getPdf($artworknumber)
     {
         $html = $this->getPdfView($artworknumber);
         return response()->view('error.pdf', [], 405);
         // return (new PdfBuilder($html))->generateArtwork($artworknumber);
     } */

    public function getInfo($artworknumber)
    {
        $object = null;
        if ((strpos($artworknumber, '.') === false) && (is_numeric($artworknumber))) {
            $object = \HamObject::find($artworknumber);
        }
        if (empty($object->objectid)) {
            $objects = \HamObject::query($artworknumber)->find();
            if (! empty($objects)) {
                $object = $objects[0];
            }
        }
        if ($object) {
            return Response::json($object);
        } else {
            return Response::json(false);
        }
    }
}
