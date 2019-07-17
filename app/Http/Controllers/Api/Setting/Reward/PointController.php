<?php

namespace App\Http\Controllers\Api\Setting\Reward;

use File;
use App\Model\Rewardable;
use App\Model\SettingReward;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PointController extends Controller
{

    public function index(Request $request)
    {
        $rewardableModels = $this->getRewardableModels();

        if ($request->filled('model')) {
            return $rewardableModels->where('model', $request->model)->first() ?: abort(404);
        }

        return $rewardableModels;
    }

    public function update(Request $request)
    {
        $setting = SettingReward::whereModel($request->model)->firstOrFail();
        $setting->update($request->except('model'));

        return $setting;
    }

    private function getRewardableModels()
    {
        $appNamespace = \Illuminate\Container\Container::getInstance()->getNamespace();
        $modelNamespace = 'Model';

        $models = collect(File::allFiles(app_path($modelNamespace)))->map(function ($item) use ($appNamespace, $modelNamespace) {
            $rel = $item->getRelativePathName();
            $class = sprintf('\%s%s%s', $appNamespace, $modelNamespace ? $modelNamespace . '\\' : '',
                implode('\\', explode('/', substr($rel, 0, strrpos($rel, '.')))));

            return class_exists($class) ? $class : null;
        });

        $rewardableModels = $models->filter(function ($model) {
            if ($model) {
                return in_array(Rewardable::class, class_implements($model));
            }

            return false;
        })->values();

        return $rewardableModels->map(function ($modelName) {
            return [
                'model' => \substr($modelName, 1),
                'action_name' => $modelName::getActionName(),
                'amount' => $modelName::getPointAmount(),
                'is_rewardable_active' => $modelName::isRewardableActive(),
            ];
        });
    }
}
