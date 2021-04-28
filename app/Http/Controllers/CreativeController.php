<?php

namespace App\Http\Controllers;

use App\Models\CreativeSet;
use App\Models\ImageSet;
use App\Models\VideoSet;
use App\Models\TitleSet;

use DB;
use Storage;
use Illuminate\Support\Facades\Gate;

class CreativeController extends Controller
{
    public function index()
    {
        return view('creatives.index', [
            'creativeSets' => auth()->user()->creativeSets
        ]);
    }

    public function create()
    {
        $type = request('type') ?? 'image';

        \Tinify\setKey(config('services.tinify.api_key'));
        \Tinify\validate();

        $compressed = \Tinify\compressionCount();

        return view('creatives.form', [
            'type' => $type,
            'compressed' => $compressed
        ]);
    }

    public function storeCreativeSets($creativeSet, $data)
    {
        \Tinify\setKey(config('services.tinify.api_key'));

        foreach ($data['creativeSets'] as $set) {
            if ($creativeSet->type == 1) {
                $imageSet = new ImageSet;

                $imageSet->image = $set['image'];

                if ($set['isTiniPNGUsed']) {
                    $imageSet->hq_image = $set['hqImage'];

                    if (!Storage::disk('images')->exists('creatives/800x800/' . md5(auth()->id()))) {
                        Storage::disk('images')->makeDirectory('creatives/800x800/' . md5(auth()->id()));
                    }

                    if (!Storage::disk('images')->exists('creatives/1200x627/' . md5(auth()->id()))) {
                        Storage::disk('images')->makeDirectory('creatives/1200x627/' . md5(auth()->id()));
                    }

                    if (!Storage::disk('images')->exists('creatives/1200x628/' . md5(auth()->id()))) {
                        Storage::disk('images')->makeDirectory('creatives/1200x628/' . md5(auth()->id()));
                    }

                    $source = \Tinify\fromFile(storage_path('app/public/images/') . $set['hqImage']);

                    $resized = $source->resize([
                        'method' => 'cover',
                        'width' => 800,
                        'height' => 800
                    ]);
                    $resized->toFile(storage_path('app/public/images/creatives/800x800/') . $set['hqImage']);

                    $resized = $source->resize([
                        'method' => 'cover',
                        'width' => 1200,
                        'height' => 627
                    ]);
                    $resized->toFile(storage_path('app/public/images/creatives/1200x627/') . $set['hqImage']);

                    $resized = $source->resize([
                        'method' => 'cover',
                        'width' => 1200,
                        'height' => 628
                    ]);
                    $resized->toFile(storage_path('app/public/images/creatives/1200x628/') . $set['hqImage']);
                } else {
                    $imageSet->hq_800x800_image = $set['hq800x800Image'];
                    $imageSet->hq_1200x627_image = $set['hq1200x627Image'];
                    $imageSet->hq_1200x628_image = $set['hq1200x628Image'];
                }

                $imageSet->save();

                $creativeSet->imageSets()->save($imageSet);
            } else if ($creativeSet->type == 2) {
                $videoSet = new VideoSet;

                $videoSet->portrait_image = $set['portraitImage'];
                $videoSet->landscape_image = $set['landscapeImage'];
                $videoSet->video = $set['video'];
                $videoSet->save();

                $creativeSet->videoSets()->save($videoSet);
            } else {
                $titleSet = new TitleSet;

                $titleSet->title = $set['title'];
                $titleSet->save();

                $creativeSet->titleSets()->save($titleSet);
            }
        }

        return [
            'compressed' => \Tinify\compressionCount()
        ];
    }

    public function store()
    {
        $data = $this->validateRequest();

        $creativeSet = new CreativeSet;
        $creativeSet->user_id = auth()->id();
        $creativeSet->name = $data['creativeSetName'];
        $creativeSet->type = $data['creativeSetType'] == 'image' ? 1 : ($data['creativeSetType'] == 'video' ? 2 : 3);
        $creativeSet->save();

        $this->storeCreativeSets($creativeSet, $data);

        return [];
    }

    public function edit(CreativeSet $creativeSet)
    {
        if (Gate::denies('modifiable', $creativeSet)) {
            return response()->json([
                'errors' => ['Not found']
            ], 404);
        }

        if ($creativeSet->type == 1) {
            $creativeSet->sets = $creativeSet->imageSets()->get();
        } else if ($creativeSet->type == 2) {
            $creativeSet->sets = $creativeSet->videoSets()->get();
        } else {
            $creativeSet->sets = $creativeSet->titleSets()->get();
        }

        \Tinify\setKey(config('services.tinify.api_key'));
        \Tinify\validate();

        $compressed = \Tinify\compressionCount();

        return view('creatives.form', [
            'type' => $creativeSet->type == 1 ? 'image' : ($creativeSet->type == 2 ? 'video' : 'title'),
            'compressed' => $compressed,
            'creativeSet' => $creativeSet
        ]);
    }

    public function update(CreativeSet $creativeSet)
    {
        if (Gate::denies('modifiable', $creativeSet)) {
            return response()->json([
                'errors' => ['Not found']
            ], 404);
        }

        $data = $this->validateRequest();

        $creativeSet->name = $data['creativeSetName'];
        $creativeSet->save();

        $creativeSet->deleteRelations();

        $this->storeCreativeSets($creativeSet, $data);

        return [];
    }

    private function validateRequest()
    {
        return request()->validate([
            'creativeSetName' => 'required|max:255',
            'creativeSetType' => 'required',
            'creativeSets' => 'required|present|array',
            'creativeSets.*.image' => 'required_if:creativeSetType,image',
            'creativeSets.*.hqImage' => 'required_if:creativeSets.*.isTiniPNGUsed,true',
            'creativeSets.*.hq800x800Image' => 'required_unless:creativeSets.*.isTiniPNGUsed,true',
            'creativeSets.*.hq1200x627Image' => 'required_unless:creativeSets.*.isTiniPNGUsed,true',
            'creativeSets.*.hq1200x628Image' => 'required_unless:creativeSets.*.isTiniPNGUsed,true',
            'creativeSets.*.portraitImage' => 'required_if:creativeSetType,video',
            'creativeSets.*.landscapeImage' => 'required_if:creativeSetType,video',
            'creativeSets.*.video' => 'required_if:creativeSetType,video',
            'creativeSets.*.title' => 'required_if:creativeSetType,title',
        ]);
    }

    public function data()
    {
        return response()->json([
            'creativeSets' => auth()->user()->creativeSets
        ]);
    }

    public function delete(CreativeSet $creativeSet)
    {
        if (Gate::denies('modifiable', $creativeSet)) {
            return response()->json([
                'errors' => ['Not found']
            ], 404);
        }

        DB::beginTransaction();

        try {
            $creativeSet->deleteRelations();
            $creativeSet->delete();

            DB::commit();
        } catch (Exception $e) {
            DB::rollback();
            return response()->json([
                'errors' => [$e->getMessage()]
            ], 500);
        }

        return [];
    }
}
