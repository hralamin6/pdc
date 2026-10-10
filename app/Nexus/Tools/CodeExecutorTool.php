<?php

namespace App\Nexus\Tools;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

class CodeExecutorTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'Execute code and return the output. Supports PHP, JavaScript, and Python. Use for running calculations, data processing, demonstrations, or testing code snippets.';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'code'     => $schema->string()->required()->description('The code to execute'),
            'language' => $schema->string()->required()->description('Programming language: php, javascript, or python'),
        ];
    }

    public function handle(Request $request): Stringable|string
    {
        $code     = $request->get('code');
        $language = strtolower(trim($request->get('language', 'php')));

        return match ($language) {
            'php'        => $this->runPhp($code),
            'javascript', 'js' => $this->runJavaScript($code),
            'python', 'py'     => $this->runPython($code),
            default      => "Unsupported language: {$language}. Supported: php, javascript, python.",
        };
    }

    private function runPhp(string $code): string
    {
        // Security: block dangerous functions
        $blocked = ['exec', 'shell_exec', 'passthru', 'system', 'popen', 'proc_open',
                    'file_get_contents', 'file_put_contents', 'unlink', 'rmdir', 'mkdir',
                    'fopen', 'fwrite', 'curl_exec', 'header', 'phpinfo', 'eval'];

        foreach ($blocked as $fn) {
            if (stripos($code, $fn) !== false) {
                return "⛔ Blocked: The function `{$fn}` is not allowed in sandbox.";
            }
        }

        // Wrap in output capture
        $wrapped = '<?php ob_start(); set_time_limit(5); ini_set("memory_limit","32M"); try { '.$code.' } catch(Throwable $e) { echo "Error: ".$e->getMessage(); } $out = ob_get_clean(); echo $out;';

        $tmpFile = sys_get_temp_dir().'/nexus_php_'.uniqid().'.php';
        file_put_contents($tmpFile, $wrapped);

        try {
            $process = new Process(['php', $tmpFile]);
            $process->setTimeout(10);
            $process->run();

            $output = $process->getOutput() ?: $process->getErrorOutput();
            return "```\n".trim($output)."\n```";
        } catch (ProcessTimedOutException) {
            return "⏱ PHP execution timed out (10s limit).";
        } finally {
            @unlink($tmpFile);
        }
    }

    private function runJavaScript(string $code): string
    {
        // Check if Node.js is available
        if (!`which node`) {
            // Return special marker for browser-side execution
            return "[[NEXUS_JS_EXEC:".base64_encode($code)."]]";
        }

        $blocked = ['require', 'fs.', 'child_process', 'exec(', 'spawn(', 'fork(', 'net.', 'http.', 'https.'];
        foreach ($blocked as $fn) {
            if (stripos($code, $fn) !== false) {
                return "⛔ Blocked: Node.js module `{$fn}` is not allowed in sandbox.";
            }
        }

        $tmpFile = sys_get_temp_dir().'/nexus_js_'.uniqid().'.js';
        $wrapped = "const console = { log: (...a) => process.stdout.write(a.join(' ')+'\\n'), error: (...a) => process.stderr.write(a.join(' ')+'\\n') };\ntry{\n{$code}\n}catch(e){console.error('Error: '+e.message)}";
        file_put_contents($tmpFile, $wrapped);

        try {
            $process = new Process(['node', '--max-old-space-size=32', $tmpFile]);
            $process->setTimeout(8);
            $process->run();

            $output = $process->getOutput() ?: $process->getErrorOutput();
            return "```\n".trim($output)."\n```";
        } catch (ProcessTimedOutException) {
            return "⏱ JavaScript execution timed out (8s limit).";
        } finally {
            @unlink($tmpFile);
        }
    }

    private function runPython(string $code): string
    {
        $pythonBin = `which python3` ? 'python3' : (`which python` ? 'python' : null);

        if (!$pythonBin) {
            return "⛔ Python is not available on this server.";
        }

        $blocked = ['import os', 'import sys', 'import subprocess', 'import shutil',
                    '__import__', 'open(', 'exec(', 'eval(', 'compile('];
        foreach ($blocked as $fn) {
            if (stripos($code, $fn) !== false) {
                return "⛔ Blocked: `{$fn}` is not allowed in Python sandbox.";
            }
        }

        $tmpFile = sys_get_temp_dir().'/nexus_py_'.uniqid().'.py';
        $wrapped = "import signal\nsignal.alarm(8)\ntry:\n".implode("\n", array_map(fn($l) => "    ".$l, explode("\n", $code)))."\nexcept Exception as e:\n    print(f'Error: {e}')";
        file_put_contents($tmpFile, $wrapped);

        try {
            $process = new Process([$pythonBin, '-B', $tmpFile]);
            $process->setTimeout(10);
            $process->run();

            $output = $process->getOutput() ?: $process->getErrorOutput();
            return "```\n".trim($output)."\n```";
        } catch (ProcessTimedOutException) {
            return "⏱ Python execution timed out (10s limit).";
        } finally {
            @unlink($tmpFile);
        }
    }
}
