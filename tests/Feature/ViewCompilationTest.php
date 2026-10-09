<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\File;
use ParseError;
use Tests\TestCase;

class ViewCompilationTest extends TestCase
{
    public function test_all_application_views_compile_to_valid_php(): void
    {
        $views = File::allFiles(resource_path('views'));
        $this->assertNotEmpty($views);

        foreach ($views as $view) {
            if (!str_ends_with($view->getFilename(), '.blade.php')) {
                continue;
            }

            try {
                $tokens = token_get_all(Blade::compileString(File::get($view->getPathname())), TOKEN_PARSE);
            } catch (ParseError $exception) {
                $this->fail($view->getRelativePathname().': '.$exception->getMessage());
            }

            $this->assertNotEmpty($tokens, $view->getRelativePathname());
        }
    }
}
