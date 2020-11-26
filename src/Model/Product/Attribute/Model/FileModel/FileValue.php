<?php

namespace CoasterCommerce\Core\Model\Product\Attribute\Model\FileModel;

use Illuminate\Http\File;

/**
 * Class FileValue
 * @package CoasterCommerce\Core\Model\Product\Attribute\Model\FileModel
 * new static() used to make sure original attribute data is not updated in product model
 */
class FileValue
{

    /**
     * @var array
     */
    public $fileData;

    /**
     * FileValue constructor.
     * @param $value
     */
    public function __construct($value = null)
    {
        $this->fileData = json_decode($value, true) ?: [];
    }

    /**
     * @return int
     */
    public function nextKey()
    {
        $nextKey = 0;
        foreach ($this->fileData as $fileConfig) {
            $nextKey = max($fileConfig['key'], $nextKey);
        }
        return $nextKey + 1;
    }

    /**
     * @param string $filePath
     * @return static
     */
    public function addFile($filePath)
    {
        $file = new File($filePath);
        $pathName = $file->getPathname();
        // remove public dir path
        $publicDirPath = public_path();
        if (strpos($file->getPathname(), $publicDirPath) === 0) {
            $pathName = substr($file->getPathname(), strlen($publicDirPath));
        }
        // save in format used for bootstrap file upload
        $newData[$pathName] = [
            'caption' => $file->getBasename(),
            'size' => $file->getSize(),
            'key' => $this->nextKey()
        ];
        $value = new static();
        $value->fileData = $this->fileData + $newData;
        return $value;
    }

    /**
     * @param array $files
     * @return static
     */
    public function addFiles($files)
    {
        $value = new static();
        $value->fileData = $this->fileData;
        foreach ($files as $file) {
            $value = $value->addFile($file);
        }
        return $value;
    }

    /**
     * @param array $files
     * @return static
     */
    public function setFiles($files)
    {
        $value = new static();
        foreach ($files as $file) {
            $value = $value->addFile($file);
        }
        return $value;
    }

    /**
     * @param int $key
     * @return static
     */
    public function deleteKey($key)
    {
        $key = (int) $key;
        $value = new static();
        foreach ($this->fileData as $file => $fileConfig) {
            if ($fileConfig['key'] !== $key) {
                $value->fileData[$file] = $fileConfig;
            }
        }
        return $value;
    }

    /**
     * @param array $keys
     * @return static
     */
    public function sortKeys($keys)
    {
        $keyOrder = array_flip($keys);
        $value = new static();
        $value->fileData = $this->fileData;
        uasort($value->fileData, function ($a, $b) use($keyOrder)  {
           return $keyOrder[$a['key']] <=> $keyOrder[$b['key']];
        });
        return $value;
    }

    /**
     * @param int $index
     * @return string
     */
    public function getFile($index)
    {
        $files = $this->getFiles();
        return array_key_exists($index, $files) ? $files[$index] : null;
    }

    /**
     * @return array
     */
    public function getFiles()
    {
        return array_keys($this->fileData);
    }

    /**
     * @return array
     */
    public function getFilesConfig()
    {
        return array_values($this->fileData);
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode($this->fileData);
    }

    /**
     * @return array
     */
    public function selectOptions()
    {
        $options = [];
        foreach ($this->fileData as $file => $fileData) {
            $options[$file] = $fileData['caption'];
        }
        return $options;
    }

}
