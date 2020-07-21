<?php

namespace backend\models;

use \DateTime;
use \ZipArchive;
use \RecursiveIteratorIterator;


use Yii;
use yii\base\Model;
use yii\data\SqlDataProvider;


/**
 * ContactForm is the model behind the contact form.
 */
class ModelEXCELZIPExporter extends Model
{
    const TEMP_FOLDER_NAME = 'uploads/reports/';
    const NUM_RECORDS_RATIO = 40000;
    public $zipFolderName;
    public $zipFolderPath;
    public $workingFolderPath;
    public $zipFileRealPath;

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function exportToExcelsZIP($params, $sql, $fileBaseName) {

        try {
            $dataProvider = new SqlDataProvider([
                'sql' => $sql,
                'params' => $params,
                'pagination' => false,
            ]);

            $this->exportToCSVsZipFolder($dataProvider->models, $fileBaseName);
            if(file_exists($this->zipFileRealPath)) {
                Yii::$app->response->sendFile(realpath($this->zipFileRealPath));
            }
            $this->deleteOldDowloadFiles();
        } catch (Exception $e) {
            echo 'Excepción capturada: ',  $e->getMessage(), "\n";
        }
    }

    function exportToCSVsZipFolder($modelRecordsArray, $fileBaseName)
    {
        $this->createTempFolder();
        $fileExtension = ".xls";

        $partitions = count($modelRecordsArray) / self::NUM_RECORDS_RATIO;
        for($i=0; $i<$partitions; $i++)
        {
            $this->array2csv(
                array_slice($modelRecordsArray, $i*self::NUM_RECORDS_RATIO, self::NUM_RECORDS_RATIO),
                $fileBaseName.'_'.($i+1).$fileExtension);
        }

        $this->createZipDownloadFolder();

        $this->zipFolderPath = "/".$this->workingFolderPath.".zip";

        return $this->zipFolderPath;
    }

    public function deleteWorkingFiles()
    {
        $path = Yii::getAlias('@webroot') . $this->zipFolderPath;

        unlink($path);
    }

    function createTempFolder()
    {
        $todaysDate = new DateTime();
        $this->zipFolderName = $todaysDate->format('YmdHisu');
        $this->workingFolderPath = self::TEMP_FOLDER_NAME.$this->zipFolderName;
        if(!file_exists(self::TEMP_FOLDER_NAME))
        {
            mkdir(self::TEMP_FOLDER_NAME);
        }
        mkdir($this->workingFolderPath);
    }

    function array2csv($array, $fileName)
    {
       $fileFolder = $this->workingFolderPath."/";
       if (count($array) == 0) {
         return null;
       }
       ob_start();
       $df = fopen($fileFolder.$fileName, 'w');
       fputcsv($df, array_keys(reset($array)), "\t", '"');
       foreach ($array as $row) {
          fputcsv($df, $row, "\t", '"');
       }
       fclose($df);
       return ob_get_clean();
    }


    function createZipDownloadFolder()
    {
        // Get real path for our folder
        $rootPath = realpath($this->workingFolderPath);
        $this->zipFileRealPath = $rootPath.'.zip';
        //echo $rootPath;
        // Initialize archive object
        $zip = new ZipArchive();
        $zip->open($this->workingFolderPath.'.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

        // Create recursive directory iterator
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($rootPath),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file)
        {
            // Skip directories (they would be added automatically)
            if (!$file->isDir())
            {
                // Get real and relative path for current file
                $filePath = $file->getRealPath();
                $relativePath = substr($filePath, strlen($rootPath) + 1);

                // Add current file to archive
                $zip->addFile($filePath, $relativePath);
            }
        }

        // Zip archive will be created only after closing object
        $zip->close();

        if (is_dir($this->workingFolderPath)) {
            $this->deleteDirRecursive($this->workingFolderPath);
        }
    }

    function deleteDirRecursive($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDirRecursive($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

    function deleteOldDowloadFiles() {
        $dirPath = self::TEMP_FOLDER_NAME;
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDirRecursive($file);
            } else {
                $filelastmodified = filemtime(realpath($file));
                if((time() - $filelastmodified) > 3600)
                {
                   unlink($file);
                }
            }
        }
    }
}
