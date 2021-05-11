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

    public function storeCreativeSets($creativeSet, $data)
    {
        \Tinify\setKey(config('services.tinify.api_key'));

        foreach ($data['creativeSets'] as $set) {
            if ($creativeSet->type == 1) {
                $imageSet = new ImageSet;

                $imageSet->image = $set['image'];
                $imageSet->optimiser = $set['optimiser'];

                if ($set['optimiser']) {
                    $imageSet->hq_image = $set['hqImage'];

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
                        $imageManager = new ImageManager();

                        $resized = $imageManager->make(storage_path('app/public/images/') . $set['hqImage'])->fit(800, 800);
                        $resized->save(storage_path('app/public/images/creatives/800x800/') . $set['hqImage'], 100);

                        $resized = $imageManager->make(storage_path('app/public/images/') . $set['hqImage'])->fit(1200, 627);
                        $resized->save(storage_path('app/public/images/creatives/1200x627/') . $set['hqImage'], 100);

                        $resized = $imageManager->make(storage_path('app/public/images/') . $set['hqImage'])->fit(1200, 628);
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
            } else if ($creativeSet->type == 3) {
                $titleSet = new TitleSet;

                $titleSet->title = $set['title'];
                $titleSet->save();

                $creativeSet->titleSets()->save($titleSet);
            } else if ($creativeSet->type == 4) {
                $descriptionSet = new DescriptionSet;

                $descriptionSet->description = $set['description'];
                $descriptionSet->save();

                $creativeSet->descriptionSets()->save($descriptionSet);
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
        $creativeSet->type = $data['creativeSetType'] == 'image' ? 1 : ($data['creativeSetType'] == 'video' ? 2 : ($data['creativeSetType'] == 'title' ? 3 : 4));
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
        } else if ($creativeSet->type == 3) {
            $creativeSet->sets = $creativeSet->titleSets()->get();
        } else {
            $creativeSet->sets = $creativeSet->descriptionSets()->get();
        }

        \Tinify\setKey(config('services.tinify.api_key'));
        \Tinify\validate();

        $compressed = \Tinify\compressionCount();

        return view('creatives.form', [
            'type' => $creativeSet->type == 1 ? 'image' : ($creativeSet->type == 2 ? 'video' : ($creativeSet->type == 3 ? 'title' : 'description')),
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

        switch ($creativeSet->type) {
            case 1:
                $creativeSet->imageSets()->delete();
                break;
            case 2:
                $creativeSet->videoSets()->delete();
                break;
            case 3:
                $creativeSet->titleSets()->delete();
                break;
            case 4:
                $creativeSet->descriptionSets()->delete();
                break;
        }

        $creativeSet->creativeSetSets()->delete();

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

    public function titleSets(CreativeSet $creativeSet)
    {
        return response()->json([
            'sets' => $creativeSet->titleSets
        ]);
    }

    public function imageSets(CreativeSet $creativeSet)
    {
        return response()->json([
            'sets' => $creativeSet->imageSets
        ]);
    }

    public function videoSets(CreativeSet $creativeSet)
    {
        return response()->json([
            'sets' => $creativeSet->videoSets
        ]);
    }

    public function descriptionSets(CreativeSet $creativeSet)
    {
        return response()->json([
            'sets' => $creativeSet->descriptionSets
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
            $creativeSet->imageSets()->delete();
            $creativeSet->videoSets()->delete();
            $creativeSet->titleSets()->delete();
            $creativeSet->descriptionSets()->delete();
            $creativeSet->creativeSetSets()->delete();
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
