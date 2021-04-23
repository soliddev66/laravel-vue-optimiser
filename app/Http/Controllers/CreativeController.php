<?php

namespace App\Http\Controllers;

use App\Models\CreativeSet;
use App\Models\MediaSet;
use App\Models\TitleSet;

use DB;
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
        $type = request('type') ?? 'media';

        return view('creatives.form', [
            'type' => $type
        ]);
    }

    public function storeCreativeSets($creativeSet, $data)
    {
        foreach ($data['creativeSets'] as $set) {
            if ($creativeSet->type == 1) {
                $mediaSet = new MediaSet;

                $mediaSet->image = $set['image'];
                $mediaSet->video = $set['video'];
                $mediaSet->save();

                $creativeSet->mediaSets()->save($mediaSet);
            } else {
                $titleSet = new TitleSet;

                $titleSet->title = $set['title'];
                $titleSet->save();

                $creativeSet->titleSets()->save($titleSet);
            }
        }
    }

    public function store()
    {
        $data = $this->validateRequest();

        $creativeSet = new CreativeSet;
        $creativeSet->user_id = auth()->id();
        $creativeSet->name = $data['creativeSetName'];
        $creativeSet->type = $data['creativeSetType'] == 'media' ? 1 : 2;
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
            $creativeSet->sets = $creativeSet->mediaSets()->get();
        } else {
            $creativeSet->sets = $creativeSet->titleSets()->get();
        }

        return view('creatives.form', [
            'type' => $creativeSet->type == 1 ? 'media' : 'title',
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
            'creativeSets.*.image' => 'required_if:creativeSetType,media',
            'creativeSets.*.title' => 'required_if:creativeSetType,title',
        ]);
    }

    public function data()
    {
        $data = auth()->user()->creativeSets();

        if (request('type') == 'media') {
            $data = $data->where('type', 1);
        }

        if (request('type') == 'title') {
            $data = $data->where('type', 2);
        }

        return response()->json([
            'creativeSets' => $data->get()
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
