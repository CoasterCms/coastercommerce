<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Model\Product\Attribute\Model\FileModel\FileValue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Exception;
use Illuminate\Support\Facades\File;

abstract class FileUploadController extends AbstractController
{

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function upload($id)
    {
        $entity = $this->_getEntity($id);
        if ($entity && $files = request()->file($this->_getRequestField())) {

            $publicDirPath = public_path($this->_getPublicSavePath($entity));
            $validTypes = $this->_validTypes();

            if (!file_exists($publicDirPath)) {
                mkdir($publicDirPath, 0777, true);
            }

            $uploadPaths = [];
            foreach ($files as $file) {
                if (!in_array($file->getMimeType(), $validTypes)) {
                    $error = 'Error, invalid file type';
                    break;
                }
                try {
                    $uniqueName = $file->getClientOriginalName();
                    $uniqueNameBase = pathinfo($uniqueName, PATHINFO_FILENAME);
                    $uniqueId = 0;
                    while(\File::exists($publicDirPath . '/' . $uniqueName)) {
                        $uniqueName = $uniqueNameBase . '_' . ++$uniqueId . '.' . $file->getClientOriginalExtension();
                    }
                    $file = $file->move($publicDirPath, $uniqueName);
                    $uploadPaths[] = $file->getPathname();
                } catch (Exception $e) {
                    $error = $e->getMessage();
                    break;
                }
            }

            if (isset($error)) {
                $output = ['error' => $error];
                foreach ($uploadPaths as $uploadPath) {
                    unlink($uploadPath);
                }
            } else {
                $oldData = $this->_getEntityFieldValue($entity)->fileData;
                $this->_saveEntity($entity, function (FileValue $attributeValue) use ($uploadPaths) {
                    return $attributeValue->addFiles($uploadPaths);
                });
                $newData = array_diff_key($this->_getEntityFieldValue($entity)->fileData, $oldData);
                $output = [
                    'initialPreview' => array_keys($newData),
                    'initialPreviewConfig' => array_values($newData),
                    'append' => true
                ];
            }

            $cacheDir = preg_replace('/^\/*uploads\//', '/cache/uploads/', $this->_getPublicSavePath($entity));
            File::deleteDirectory(public_path($cacheDir));

        } else {
            $output = ['error' => 'No files found for upload or item does not exist.'];
        }

        return response()->json($output);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function delete($id)
    {
        $output = [];
        try {
            if ($deleteKey = request()->post('key')) {
                $this->_saveEntity($this->_getEntity($id), function (FileValue $attributeValue) use ($deleteKey) {
                    return $attributeValue->deleteKey($deleteKey);
                });
            }
        } catch (Exception $e) {
            $output = ['error' => true];
        }
        return response()->json($output);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    public function sort($id)
    {
        $output = [];
        try {
            if ($stack = request()->post('stack')) {
                $this->_saveEntity($this->_getEntity($id), function (FileValue $attributeValue) use ($stack) {
                    return $attributeValue->sortKeys(array_map(function($config) {
                        return $config['key'];
                    }, $stack));
                });
            }
        } catch (Exception $e) {
            $output = ['error' => true];
        }
        return response()->json($output);
    }

    /**
     * @return array
     */
    protected function _validTypes()
    {
        return ['image/png', 'image/gif', 'image/jpg', 'image/jpeg'];
    }

    /**
     * @return string
     */
    protected function _getRequestField()
    {
        return $this->_getModelField();
    }

    /**
     * @return string
     */
    abstract protected function _getModelField();

    /**
     * @param Model $entity
     * @return string
     */
    abstract protected function _getPublicSavePath($entity);

    /**
     * @param int $id
     * @return Model
     */
    abstract protected function _getEntity($id);

    /**
     * @param Model $entity
     * @return FileValue
     */
    protected function _getEntityFieldValue($entity)
    {
        $value = $entity->{$this->_getModelField()};
        return $value && is_a($value, FileValue::class) ? $value : new FileValue($value);
    }

    /**
     * @param Model $entity
     * @param callable $callback
     */
    protected function _saveEntity($entity, $callback)
    {
        $entity->{$this->_getModelField()} = $callback($this->_getEntityFieldValue($entity));
        $entity->save();
    }

}
