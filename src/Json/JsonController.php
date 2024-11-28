<?php
namespace Json;

class JsonController {

    private string $filePath;

    public function __construct($filePath) {
        $this->filePath = $filePath;
    }

    public function getFileContent(): array {
        if (file_exists($this->filePath)) {
            $jsonContent = file_get_contents($this->filePath);
            $decodedContent = json_decode($jsonContent, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON in file: " . json_last_error_msg());
            }

            return $decodedContent;
        } else {
            throw new \Exception("File Not Found");
        }
    }

    public function findByKey($key) {
        $decodedContent = $this->getFileContent();
        return $this->searchKeyRecursively($decodedContent, $key);
    }

    private function searchKeyRecursively($array, $key) {
        if (is_array($array)) {
            foreach ($array as $currentKey => $value) {
                if ($currentKey === $key) {
                    return $value;
                }

                if (is_array($value) || is_object($value)) {
                    $found = $this->searchKeyRecursively($value, $key);
                    if ($found !== null) {
                        return $found;
                    }
                }
            }
        }
        return null;
    }

    public function saveToFile(array $data): bool {
        $jsonContent = json_encode($data, JSON_PRETTY_PRINT);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Error encoding JSON: " . json_last_error_msg());
        }

        if (file_put_contents($this->filePath, $jsonContent) === false) {
            throw new \Exception("Unable to write to file.");
        }

        return true;
    }

    public function addKeyValue($key, $value): bool {
        $data = $this->getFileContent();
        $data[$key] = $value;
        
        return $this->saveToFile($data);
    }

    public function deleteByKey($key): bool {
        $data = $this->getFileContent();

        if (array_key_exists($key, $data)) {
            unset($data[$key]);
            return $this->saveToFile($data);
        }

        throw new \Exception("Key not found in the file.");
    }

    public function getAllKeys(): array {
        $data = $this->getFileContent();
        return $this->extractKeysRecursively($data);
    }

    private function extractKeysRecursively($array): array {
        $keys = [];

        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $keys[] = $key;
                if (is_array($value) || is_object($value)) {
                    $keys = array_merge($keys, $this->extractKeysRecursively($value));
                }
            }
        }

        return $keys;
    }

    public function getFileSize(): int {
        if (!file_exists($this->filePath)) {
            throw new \Exception("File not found.");
        }

        return filesize($this->filePath);
    }

    public function isFileEmpty(): bool {
        return filesize($this->filePath) === 0;
    }

    public function validateJson(): bool {
        $jsonContent = file_get_contents($this->filePath);
        $decodedContent = json_decode($jsonContent, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception("Invalid JSON: " . json_last_error_msg());
        }

        return true;
    }

    public function getFilePath() {
        return $this->filePath;
    }

    public function setFilePath($filePath) {
        $this->filePath = $filePath;
        return $this;
    }
}

?>