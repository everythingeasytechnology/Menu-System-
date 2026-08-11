<?php

namespace App\Services;

use App\Models\PresetFoodImage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class MenuImageService
{
    /**
     * Resolve the preset_food_image_id to save on a menu item from an upload,
     * an existing preset selection, a remove flag, or leave the current value untouched.
     */
    public function resolvePresetImageId(Request $request, string $itemName, string $categoryName, ?int $currentPresetImageId = null): ?int
    {
        if ($request->hasFile('image')) {
            $path = $this->compressAndStoreImage($request->file('image'));

            $preset = PresetFoodImage::create([
                'name' => $itemName,
                'tags' => strtolower($itemName.', '.$categoryName),
                'image_path' => 'storage/'.$path,
            ]);

            return $preset->id;
        }

        if ($request->filled('preset_image_id')) {
            return (int) $request->input('preset_image_id');
        }

        if ($request->input('remove_image') === '1') {
            return null;
        }

        return $currentPresetImageId;
    }

    /**
     * Compress an uploaded image using GD and store it under storage/app/public/menu_items.
     */
    public function compressAndStoreImage(UploadedFile $file): string
    {
        $filename = uniqid().'.'.$file->getClientOriginalExtension();
        $directory = storage_path('app/public/menu_items');

        if (! file_exists($directory)) {
            mkdir($directory, 0755, true);
        }

        $destinationPath = $directory.'/'.$filename;
        $sourcePath = $file->getRealPath();

        $info = @getimagesize($sourcePath);
        if (! $info) {
            return $file->store('menu_items', 'public');
        }

        $mime = $info['mime'];

        try {
            if ($mime === 'image/jpeg' || $mime === 'image/jpg') {
                $image = @imagecreatefromjpeg($sourcePath);
                if ($image) {
                    @imagejpeg($image, $destinationPath, 80);
                    @imagedestroy($image);
                } else {
                    copy($sourcePath, $destinationPath);
                }
            } elseif ($mime === 'image/png') {
                $image = @imagecreatefrompng($sourcePath);
                if ($image) {
                    @imagealphablending($image, false);
                    @imagesavealpha($image, true);
                    @imagepng($image, $destinationPath, 6);
                    @imagedestroy($image);
                } else {
                    copy($sourcePath, $destinationPath);
                }
            } elseif ($mime === 'image/gif') {
                $image = @imagecreatefromgif($sourcePath);
                if ($image) {
                    @imagegif($image, $destinationPath);
                    @imagedestroy($image);
                } else {
                    copy($sourcePath, $destinationPath);
                }
            } else {
                copy($sourcePath, $destinationPath);
            }
        } catch (\Exception $e) {
            copy($sourcePath, $destinationPath);
        }

        return 'menu_items/'.$filename;
    }
}
