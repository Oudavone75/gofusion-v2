<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Route;

if (!function_exists('generateOtp')) {
    /**
     * @return mixed
     **/
    function generateOtp($length = 4)
    {
        $min = pow(10, $length - 1);
        $max = pow(10, $length) - 1;
        return rand($min, $max);
    }
}

if (!function_exists('generateToken')) {
    /**
     * @return mixed
     */
    function generateToken(): string
    {
        return md5(rand(1, 10) . microtime());
    }
}
if (!function_exists('paginationData')) {
    /**
     * @return mixed
     **/
    function paginationData(
        $data,
        $total = 0,
        $perPage = 0,
        $currentPage = 0,
        $lastPage = 0,
        $from = 0,
        $to = 0,
        $paramName = 'page'
    ) {
        return [
            'result' => $data,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'from' => $from,
            'to' => $to,
            'param_name' => $paramName
        ];
    }
}

if (!function_exists('uploadBase64File')) {
    function uploadBase64File($image, $disk, $path, $oldImage = null)
    {
        try {
            if ($oldImage) {
                $oldFilePath = $oldImage;
                if (preg_match('/^https?:\/\/[\w\-\.]+\.\w{2,}(\/\S*)?$/', $oldFilePath)) {
                    $filename = basename($oldFilePath);
                    if (Storage::disk($disk)->exists($path . '/' . $filename)) {
                        Storage::disk($disk)->delete($path . '/' . $filename);
                    }
                }
            }
            $ext = str_replace('data:image/', '', explode(';', $image)[0]);
            $imageData = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $image));
            $filename = 'image_' . Str::random(10) . '_' . time() . '.' . $ext;
            Storage::disk($disk)->put($path . '/' . $filename, $imageData);
            return $filename;
        } catch (\Throwable $exception) {
            return null;
        }
    }
}

if (!function_exists('deleteFile')) {
    function deleteFile($path)
    {
        $filePath = public_path($path);
        if (File::exists($filePath)) {
            File::delete($filePath);
            return true;
        }
        return false;
    }
}

if (!function_exists('uploadFile')) {
    function uploadFile($file, $disk, $path, $name = "")
    {
        $filename = $name . time() . '.' . $file->getClientOriginalExtension();
        $file->storeAs($path, $filename, $disk);
        return $filename;
    }
}

if (!function_exists('formatNotificationCount')) {
    function formatNotificationCount($count)
    {
        if ($count >= 1000000) {
            return round($count / 1000000, 1) . 'M';
        } elseif ($count >= 1000) {
            return round($count / 1000, 1) . 'K';
        }
        return $count;
    }
}

if (!function_exists('getImage')) {
    function getImage($image)
    {
        return empty($image) ? 'profile.jpg' : $image;
    }
}

if (!function_exists('generateSlug')) {
    function generateSlug($string)
    {
        $slug = strtolower($string);
        $slug = preg_replace('/[^a-z0-9\s-]/', '', $slug);
        $slug = preg_replace('/[\s-]+/', '-', $slug);
        $slug = trim($slug, '-');

        return $slug;
    }
}

if (!function_exists('ajaxResponse')) {
    function ajaxResponse($status = true, $message = '', $data = [])
    {
        return [
            'status' => $status,
            'message' => $message,
            'data' => $data
        ];
    }
}

if (!function_exists('activeCampaignSeasonFilter')) {
    function activeCampaignSeasonFilter()
    {
        $type = request()->get('type', 'campaign');
        return in_array($type, ['campaign', 'season']) ? $type : 'campaign';
    }
}

if (!function_exists('formatedDateTime')) {
    function formatedDateTime(Carbon $date): string
    {
        if ($date->isToday()) {
            return 'Today ' . $date->format('h:i A');
        }

        if ($date->isYesterday()) {
            return 'Yesterday ' . $date->format('h:i A');
        }

        $diffInDays = $date->diffInDays();

        if ($diffInDays < 7) {
            // Within a week
            return $date->diffForHumans();
        }

        if ($diffInDays < 30) {
            // Within a month → weeks
            $weeks = floor($diffInDays / 7);
            return $weeks <= 1 ? '1 week ago' : $weeks . ' weeks ago';
        }

        $diffInMonths = $date->diffInMonths(); // always integer

        if ($diffInMonths < 12) {
            // Within a year → months
            return $diffInMonths <= 1 ? '1 month ago' : round($diffInMonths) . ' months ago';
        }

        if ($date->isCurrentYear()) {
            // Same year
            return $date->format('M d, h:i A');
        }

        // Older than this year
        return $date->format('M d, Y h:i A');
    }
}

if (! function_exists('breadcrumbs')) {
    function breadcrumbs(array $extra = [])
    {
        $paths = [];

        // Detect if current route belongs to admin or company_admin
        $routeName = Route::currentRouteName();

        $prefix = null;

        if (str_starts_with($routeName, 'admin.')) {
            $prefix = 'admin';
            $paths[] = [
                'name' => 'Dashboard',
                'url' => route('admin.dashboard'),
                'is_active' => false,
            ];
        } elseif (str_starts_with($routeName, 'company_admin.')) {
            $prefix = 'company_admin';
            $paths[] = [
                'name' => 'Dashboard',
                'url' => route('company_admin.dashboard'),
                'is_active' => false,
            ];
        }

        if ($routeName && $prefix) {
            $parts = explode('.', $routeName);

            // Skip prefix
            if ($parts[0] === $prefix) {
                array_shift($parts);
            }

            $labels = [
                'list'    => 'List',
                'create'  => 'Create',
                'edit'    => 'Edit',
                'show'    => 'View',
                'delete'  => 'Delete',
                'store'   => 'Store',
                'update'  => 'Update',
            ];

            $builtRoute = $prefix;
            foreach ($parts as $index => $part) {
                $builtRoute .= '.' . $part;

                $isLast = $index === array_key_last($parts);

                // Try to find a valid route for non-last items
                $url = null;
                if (!$isLast) {
                    // Try common route patterns for your naming convention
                    $possibleRoutes = [
                        $builtRoute . '.list',    // e.g., admin.posts.list
                        $builtRoute . '.index',   // fallback to index
                        $builtRoute,              // e.g., admin.posts
                    ];

                    foreach ($possibleRoutes as $testRoute) {
                        if (Route::has($testRoute)) {
                            $url = route($testRoute);
                            break;
                        }
                    }

                    // Fallback
                    if (!$url) {
                        $url = 'javascript:history.back()';
                    }
                }

                $paths[] = [
                    'name'      => $labels[$part] ?? (ucfirst($part) == 'Index' ? 'List' : ucfirst($part)),
                    'url'       => $url,
                    'is_active' => $isLast,
                ];
            }
        }

        return array_merge($paths, $extra);
    }
}

if (! function_exists('isUrl')) {
    function isUrl($string)
    {
        if (!is_string($string) || trim($string) === '') {
            return false;
        }

        // Use PHP's built-in filter first
        if (filter_var($string, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        // Parse components for stricter validation
        $parts = parse_url($string);

        if ($parts === false) {
            return false;
        }

        // Must have a valid scheme
        $validSchemes = ['http', 'https', 'ftp'];
        if (!isset($parts['scheme']) || !in_array(strtolower($parts['scheme']), $validSchemes, true)) {
            return false;
        }

        // Must have a host (domain or IP)
        if (empty($parts['host'])) {
            return false;
        }

        // Validate domain or IP
        if (
            !filter_var($parts['host'], FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME) &&
            !filter_var($parts['host'], FILTER_VALIDATE_IP)
        ) {
            return false;
        }

        return true;
    }
}

if (! function_exists('userLanguage')) {
    function userLanguage(int $userId): string
    {
        $userDetail = \App\Models\UserDetail::with('language')
            ->where('user_id', $userId)
            ->first();

        return optional($userDetail->language)->slug ?? app()->getLocale();
    }
}

if (! function_exists('parseFlexibleDate')) {
    function parseFlexibleDate($dateString)
    {
        if (empty($dateString)) return null;

        // Clean up spaces and separators
        $dateString = trim(preg_replace('/\s+/', ' ', $dateString));
        $dateString = str_replace('-', '/', $dateString); // unify separators

        // Possible formats to try
        $formats = [
            'd/m/y H:i',
            'd/m/Y H:i',
            'd/m/y',
            'd/m/Y',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $dateString)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // try next format
            }
        }

        // As last resort, try Carbon::parse (handles natural formats)
        try {
            return Carbon::parse($dateString)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            dd("❌ Unable to parse date: '{$dateString}'");
        }
    }
}

/**
 * Parse and return only the company name
 */
function getCompanyName(string $string): ?string
{
    $parsed = parseCompanyString($string);
    return $parsed['company_name'];
}

/**
 * Parse and return only the role
 */
function getCompanyRole(string $string): ?string
{
    $parsed = parseCompanyString($string);
    return $parsed['role'];
}

if (! function_exists('parseFlexibleDate')) {
    function parseFlexibleDate($dateString)
    {
        if (empty($dateString)) return null;

        // Clean up spaces and separators
        $dateString = trim(preg_replace('/\s+/', ' ', $dateString));
        $dateString = str_replace('-', '/', $dateString); // unify separators

        // Possible formats to try
        $formats = [
            'd/m/y H:i',
            'd/m/Y H:i',
            'd/m/y',
            'd/m/Y',
        ];

        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $dateString)->format('Y-m-d H:i:s');
            } catch (\Exception $e) {
                // try next format
            }
        }

        // As last resort, try Carbon::parse (handles natural formats)
        try {
            return Carbon::parse($dateString)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            dd("❌ Unable to parse date: '{$dateString}'");
        }
    }
}

/**
 * Parse company string format: "company : Inéo - code : INO263"
 *
 * @param string $string The input string to parse
 * @return array Returns associative array with role, company_name, and code
 */
function parseCompanyString(string $string): array
{
    $result = [
        'role' => null,
        'company_name' => null,
        'code' => null,
    ];

    // Split by " - " to separate the two main parts
    $parts = explode(' - ', $string);

    if (count($parts) >= 2) {
        // Parse first part: "company : Inéo"
        $firstPart = explode(':', $parts[0], 2);
        if (count($firstPart) === 2) {
            $result['role'] = trim($firstPart[0]); // "company"
            $result['company_name'] = trim($firstPart[1]); // "Inéo"
        }

        // Parse second part: "code : INO263"
        $secondPart = explode(':', $parts[1], 2);
        if (count($secondPart) === 2) {
            $result['code'] = trim($secondPart[1]); // "INO263"
        }
    }

    return $result;
}

/**
 * Parse and return only the code
 */
function getCompanyCode(string $string): ?string
{
    $parsed = parseCompanyString($string);
    return $parsed['code'];
}

function extractUsernameFromEmail($email)
{
    $username = explode('@', $email)[0];
    return $username;
}

if (!function_exists('format_number_short')) {
    function format_number_short($number)
    {
        if ($number < 1000) {
            return $number;
        }

        $suffixes = ['', 'K', 'M', 'B', 'T'];
        $suffixIndex = 0;

        while ($number >= 1000 && $suffixIndex < count($suffixes) - 1) {
            $number /= 1000;
            $suffixIndex++;
        }

        $formatted = $number == floor($number) ? number_format($number, 0) : number_format($number, 1);

        return $formatted . $suffixes[$suffixIndex];
    }
}

function convertExcelDate($value) {
    // If numeric → treat as Excel date
    if (is_numeric($value)) {
        return Carbon::instance(Date::excelToDateTimeObject($value))
                ->format('Y-m-d H:i:s');
    }

    // If string → try normal parsing
    if (is_string($value) && !empty(trim($value))) {
        try {
            return Carbon::parse($value)->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return null;
        }
    }

    // If empty or invalid
    return null;
}
