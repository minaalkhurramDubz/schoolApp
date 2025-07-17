<?php

namespace App\Jobs;

use App\Imports\UserImport;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\URL;
use Maatwebsite\Excel\Facades\Excel;

// larael uses shuld queu to put the job on schedule 
class ProcessUserImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $path;

    protected $role;

    protected $schoolId;

    public function __construct($path, $role, $schoolId)
    {
        $this->path = $path;
        $this->role = $role;
        $this->schoolId = $schoolId;
    }

    // handle function executes when job starts 
    public function handle(): void
    {
        try {
            // read the excel path
            $path = storage_path('app/'.$this->path);

            \Log::info("Checking file at: {$path}");

            if (! file_exists($path)) {
                \Log::error('File not found: '.$path);

                return;
            }

            \Log::info('File exists! Proceeding with import...');
          // import the excel part , use user import php for data 
          // grabs data from excel file and stores thm in. rows 
            $import = new UserImport;
            Excel::import($import, $path);

            $rows = $import->rows;

            if ($rows->isEmpty()) {
                \Log::warning('Excel import returned empty.');

                return;
            }

            \Log::info('Imported rows:', $rows->toArray());

            // loop over every row ,each row is a record 
            foreach ($rows as $row) {
                $email = $row['email'] ?? null;
                $name = $row['name'] ?? null;

                if (! $email || ! $name) {
                    continue;
                }

                // First or create user
                // create the user if it doesnt exist otherwise update it 
                $user = User::firstOrCreate(
                    ['email' => $email],
                    [
                        'name' => $name,
                        'password' => bcrypt(str()->random(16)),
                        'role' => $this->role,
                        'school_id' => $this->schoolId,
                    ]
                );
                // Assign Spatie role if missing
                if (! $user->hasRole($this->role)) {
                    $user->assignRole($this->role);
                }

                // attach the user to the associated school
                // sync without detaching avoids duplicate rows in the pivot table
                $user->schools()->syncWithoutDetaching([
                    $this->schoolId => ['role' => $this->role],
                ]);

                URL::forceRootUrl('http://127.0.0.1:8000');
                // Generate signed login link
                $url = URL::temporarySignedRoute(
                    'filament.auth.auth.login',
                    now()->addMinutes(15),
                    ['email' => $user->email]
                );

                // Send raw email
                \Mail::raw(
                    "Click to login: {$url}",
                    function ($message) use ($user) {
                        $message->to($user->email)
                            ->subject('Your Magic Login Link');
                    }
                );

                \Log::info("Sent magic link email to {$user->email}");
            }
        } catch (\Throwable $e) {
            \Log::error('Job failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }

    }
}
