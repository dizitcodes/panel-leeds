<?php

namespace Dizit\Panel\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SetupCommand extends BaseCommand
{
    protected $group = 'Dizit';
    protected $name = 'panel:leads-install';
    protected $description = 'Configura os leads no painel.';

    public function run(array $params)
    {
        CLI::write('Configurando os leads no painel...', 'green');

        $directories = [
            // 'Cells' => 'Cells/',
            'Models' => 'Models/',
            'Views' => 'Views/',
            'Controllers' => 'Controllers/',
            'Database' => 'Database',
            // 'Helpers' => 'Helpers/',
            'Libraries' => 'Libraries/',
            // Adicione mais diretórios conforme necessário
        ];


        foreach ($directories as $key => $relativePath) {
            $this->copyFilesFromDirectory($key, $relativePath);
        }

        $this->addRoute("\n\n");
        $this->addRoute("\$routes->resource('admin/configuracoes', ['controller' => 'Admin\\Configuracoes', 'filter' => 'admin_auth']);");
    }

    private function copyFilesFromDirectory($directoryName, $relativePath)
    {
        $sourceDir = VENDORPATH . 'dizitcodes/panel/src/' . $directoryName . '/';
        $destinationDir = APPPATH . $relativePath;

        $this->recursiveCopy($sourceDir, $destinationDir);
    }

    private function recursiveCopy($src, $dst)
    {
        $dir = opendir($src);
        if (!is_dir($dst)) {
            mkdir($dst, 0755, true);
        }

        while (($file = readdir($dir)) !== false) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->recursiveCopy($src . '/' . $file, $dst . '/' . $file);
                    // Opcional: remover o diretório de origem após copiar seu conteúdo
                    // rmdir($src . '/' . $file);
                } else {
                    $sourceFile = $src . '/' . $file;
                    $destinationFile = $dst . '/' . $file;
                    if (!file_exists($destinationFile)) {
                        if (!copy($sourceFile, $destinationFile)) {
                            CLI::write("Falha ao copiar {$sourceFile}...", 'red');
                        } else {
                            CLI::write("{$file} copiado com sucesso para {$destinationFile}", 'green');
                            // Excluir o arquivo de origem após a cópia
                            unlink($sourceFile);
                        }
                    } else {
                        CLI::write("{$file} já existe em {$destinationFile}. Nenhuma ação foi tomada.", 'yellow');
                        unlink($sourceFile);
                    }
                }
            }
        }
        closedir($dir);
    }


    // private function addRoute($routeDefinition)
    // {
    //     $routesPath = APPPATH . 'Config/Routes.php';

    //     // Verifique se o arquivo de rotas existe
    //     if (!file_exists($routesPath)) {
    //         CLI::write("O arquivo de rotas não foi encontrado em {$routesPath}", 'red');
    //         return;
    //     }

    //     // Verifique se a linha já existe no arquivo
    //     $contents = file_get_contents($routesPath);
    //     if (strpos($contents, $routeDefinition) !== false) {
    //         CLI::write('A rota especificada já existe no arquivo de rotas.', 'yellow');
    //         return;
    //     }

    //     // Adicione a linha ao final do arquivo
    //     if (!file_put_contents($routesPath, PHP_EOL . $routeDefinition, FILE_APPEND)) {
    //         CLI::write("Não foi possível escrever no arquivo de rotas em {$routesPath}", 'red');
    //     } else {
    //         CLI::write('A rota foi adicionada com sucesso.', 'green');
    //     }
    // }

    private function addRoute($routeDefinition)
    {
        $routesPath = APPPATH . 'Config/Routes.php';

        // Verifique se o arquivo de rotas existe
        if (!file_exists($routesPath)) {
            CLI::write("O arquivo de rotas não foi encontrado em {$routesPath}", 'red');
            return;
        }

        // Obter o conteúdo do arquivo de rotas
        $contents = file_get_contents($routesPath);

        // Verifique se existe o marcador de início
        $startMarker = "// START - Auto Routes - Packages";
        $startPos = strpos($contents, $startMarker);
        if ($startPos === false) {
            CLI::write("O marcador de início das rotas não foi encontrado em {$routesPath}", 'red');
            return;
        }

        // Encontrar a posição do marcador de fim
        $endMarker = "// END - Auto Routes - Packages";
        $endPos = strpos($contents, $endMarker, $startPos);
        if ($endPos === false) {
            CLI::write("O marcador de fim das rotas não foi encontrado em {$routesPath}", 'red');
            return;
        }

        // Criar a nova definição de rota com quebra de linha
        $newRouteDefinition = PHP_EOL . $routeDefinition;

        // Inserir a nova definição de rota entre os marcadores
        $updatedContents = substr_replace($contents, $newRouteDefinition, $endPos, 0);

        // Escrever o conteúdo atualizado de volta ao arquivo de rotas
        if (!file_put_contents($routesPath, $updatedContents)) {
            CLI::write("Não foi possível escrever no arquivo de rotas em {$routesPath}", 'red');
        } else {
            CLI::write('A rota foi adicionada com sucesso.', 'green');
        }
    }
}
