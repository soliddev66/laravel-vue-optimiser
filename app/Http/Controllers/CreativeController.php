<?php

namespace App\Http\Controllers;

use App\Models\CreativeSet;
use App\Models\ImageSet;
use App\Models\VideoSet;
use App\Models\TitleSet;
use App\Models\DescriptionSet;

use DB;
use Storage;
use Illuminate\Support\Facades\Gate;

use Intervention\Image\ImageManager;

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

    public function storeCreativeSets($creative_set, $data)
    {
        \Tinify\setKey(config('services.tinify.api_key'));

        foreach ($data['creativeSets'] as $set) {
            if ($creative_set->type == 1) {
                $image_set = new ImageSet;

                $image_set->image = $set['image'];
                $image_set->optimiser = $set['optimiser'];

                if ($set['optimiser']) {
                    $image_set->hq_image = $set['hqImage'];

                    $directory = explode('/', $set['hqImage']);
                    array_pop($directory);

                    if (count($directory) > 0) {
                        $directory = implode('/', $directory);
                    } else {
                        $directory = '';
                    }

                    if (!Storage::disk('images')->exists('creatives/800x800/' . $directory)) {
                        Storage::disk('images')->makeDirectory('creatives/800x800/' . $directory);
                    }

                    if (!Storage::disk('images')->exists('creatives/1200x627/' . $directory)) {
                        Storage::disk('images')->makeDirectory('creatives/1200x627/' . $directory);
                    }

                    if (!Storage::disk('images')->exists('creatives/1200x628/' . $directory)) {
                        Storage::disk('images')->makeDirectory('creatives/1200x628/' . $directory);
                    }

                    if ($set['optimiser'] == 1) {
                        $image_manager = new ImageManager();

                        $resized = $image_manager->make(storage_path('app/public/images/') . $set['hqImage'])->fit(800, 800);
                        $resized->save(storage_path('app/public/images/creatives/800x800/') . $set['hqImage'], 100);

                        $resized = $image_manager->make(storage_path('app/public/images/') . $set['hqImage'])->fit(1200, 627);
                        $resized->save(storage_path('app/public/images/creatives/1200x627/') . $set['hqImage'], 100);

                        $resized = $image_manager->make(storage_path('app/public/images/') . $set['hqImage'])->fit(1200, 628);
                        $resized->save(storage_path('app/public/images/creatives/1200x628/') . $set['hqImage'], 100);
                    } else {
                        $source = \Tinify\fromFile(storage_path('app/public/images/') . $set['hqImage']);

                        $resized = $source->resize(['method' => 'cover', 'width' => 800, 'height' => 800]);
                        $resized->toFile(storage_path('app/public/images/creatives/800x800/') . $set['hqImage']);

                        $resized = $source->resize(['method' => 'cover', 'width' => 1200, 'height' => 627]);
                        $resized->toFile(storage_path('app/public/images/creatives/1200x627/') . $set['hqImage']);

                        $resized = $source->resize(['method' => 'cover', 'width' => 1200, 'height' => 628]);
                        $resized->toFile(storage_path('app/public/images/creatives/1200x628/') . $set['hqImage']);
                    }
                } else {
                    $image_set->hq_800x800_image = $set['hq800x800Image'];
                    $image_set->hq_1200x627_image = $set['hq1200x627Image'];
                    $image_set->hq_1200x628_image = $set['hq1200x628Image'];
                }

                $image_set->save();

                $creative_set->imageSets()->save($image_set);
            } else if ($creative_set->type == 2) {
                $video_set = new VideoSet;

                $video_set->portrait_image = $set['portraitImage'];
                $video_set->landscape_image = $set['landscapeImage'];
                $video_set->video = $set['video'];
                $video_set->save();

                $creative_set->videoSets()->save($video_set);
            } else if ($creative_set->type == 3) {
                $title_set = new TitleSet;

                $title_set->title = $set['title'];
                $title_set->save();

                $creative_set->titleSets()->save($title_set);
            } else if ($creative_set->type == 4) {
                $description_set = new DescriptionSet;

                $description_set->description = $set['description'];
                $description_set->save();

                $creative_set->descriptionSets()->save($description_set);
            }
        }

        return [
            'compressed' => \Tinify\compressionCount()
        ];
    }

    public function store()
    {
        $data = $this->validateRequest();

        $creative_set = new CreativeSet;
        $creative_set->user_id = auth()->id();
        $creative_set->name = $data['creativeSetName'];
        $creative_set->type = $data['creativeSetType'] == 'image' ? 1 : ($data['creativeSetType'] == 'video' ? 2 : ($data['creativeSetType'] == 'title' ? 3 : 4));
        $creative_set->save();

        $this->storeCreativeSets($creative_set, $data);

        return [];
    }

    public function edit(CreativeSet $creative_set)
    {
        if (Gate::denies('modifiable', $creative_set)) {
            return response()->json([
                'errors' => ['Not found']
            ], 404);
        }

        if ($creative_set->type == 1) {
            $creative_set->sets = $creative_set->imageSets()->get();
        } else if ($creative_set->type == 2) {
            $creative_set->sets = $creative_set->videoSets()->get();
        } else if ($creative_set->type == 3) {
            $creative_set->sets = $creative_set->titleSets()->get();
        } else {
            $creative_set->sets = $creative_set->descriptionSets()->get();
        }

        \Tinify\setKey(config('services.tinify.api_key'));
        \Tinify\validate();

        $compressed = \Tinify\compressionCount();

        return view('creatives.form', [
            'type' => $creative_set->type == 1 ? 'image' : ($creative_set->type == 2 ? 'video' : ($creative_set->type == 3 ? 'title' : 'description')),
            'compressed' => $compressed,
            'creativeSet' => $creative_set
        ]);
    }

    public function update(CreativeSet $creative_set)
    {
        if (Gate::denies('modifiable', $creative_set)) {
            return response()->json([
                'errors' => ['Not found']
            ], 404);
        }

        $data = $this->validateRequest();

        $creative_set->name = $data['creativeSetName'];
        $creative_set->save();

        switch ($creative_set->type) {
            case 1:
                $creative_set->imageSets()->delete();
                break;
            case 2:
                $creative_set->videoSets()->delete();
                break;
            case 3:
                $creative_set->titleSets()->delete();
                break;
            case 4:
                $creative_set->descriptionSets()->delete();
                break;
        }

        $creative_set->creativeSetSets()->delete();

        $this->storeCreativeSets($creative_set, $data);

        return [];
    }

    private function validateRequest()
    {
        return request()->validate([
            'creativeSetName' => 'required|max:255',
            'creativeSetType' => 'required',
            'creativeSets' => 'required|present|array',
            'creativeSets.*.image' => 'required_if:creativeSetType,image',
            'creativeSets.*.hqImage' => function ($attribute, $value, $fail) {
                if (request()->input('creativeSetType') == 'image' && request()->input('creativeSets')[explode('.', $attribute)[1]]['optimiser'] && empty($value)) {
                    $fail('The ' . $attribute . ' is required.');
                }
            },
            'creativeSets.*.hq800x800Image' => function ($attribute, $value, $fail) {
                if (request()->input('creativeSetType') == 'image' && !request()->input('creativeSets')[explode('.', $attribute)[1]]['optimiser'] && empty($value)) {
                    $fail('The ' . $attribute . ' is required.');
                }
            },
            'creativeSets.*.hq1200x627Image' => function ($attribute, $value, $fail) {
                if (request()->input('creativeSetType') == 'image' && !request()->input('creativeSets')[explode('.', $attribute)[1]]['optimiser'] && empty($value)) {
                    $fail('The ' . $attribute . ' is required.');
                }
            },
            'creativeSets.*.hq1200x628Image' => function ($attribute, $value, $fail) {
                if (request()->input('creativeSetType') == 'image' && !request()->input('creativeSets')[explode('.', $attribute)[1]]['optimiser'] && empty($value)) {
                    $fail('The ' . $attribute . ' is required.');
                }
            },
            'creativeSets.*.portraitImage' => 'required_if:creativeSetType,video',
            'creativeSets.*.landscapeImage' => 'required_if:creativeSetType,video',
            'creativeSets.*.video' => 'required_if:creativeSetType,video',
            'creativeSets.*.title' => 'required_if:creativeSetType,title',
            'creativeSets.*.description' => 'required_if:creativeSetType,description',
        ]);
    }

    public function data()
    {
        return response()->json([
            'creativeSets' => auth()->user()->creativeSets
        ]);
    }

    public function titleSets(CreativeSet $creative_set)
    {
        return response()->json([
            'sets' => $creative_set->titleSets
        ]);
    }

    public function imageSets(CreativeSet $creative_set)
    {
        return response()->json([
            'sets' => $creative_set->imageSets
        ]);
    }

    public function videoSets(CreativeSet $creative_set)
    {
        return response()->json([
            'sets' => $creative_set->videoSets
        ]);
    }

    public function descriptionSets(CreativeSet $creative_set)
    {
        return response()->json([
            'sets' => $creative_set->descriptionSets
        ]);
    }

    public function delete(CreativeSet $creative_set)
    {
        if (Gate::denies('modifiable', $creative_set)) {
            return response()->json([
                'errors' => ['Not found']
            ], 404);
        }

        DB::beginTransaction();

        try {
            $creative_set->imageSets()->delete();
            $creative_set->videoSets()->delete();
            $creative_set->titleSets()->delete();
            $creative_set->descriptionSets()->delete();
            $creative_set->creativeSetSets()->delete();
            $creative_set->delete();

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
