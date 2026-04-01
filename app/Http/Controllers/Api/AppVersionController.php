<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateAppVersionRequest;
use App\Models\AppVersion;
use App\Traits\ApiResponse;

class AppVersionController extends Controller
{
    use ApiResponse;

    public function __construct(private AppVersion $appVersion) {}
    public function getAllVersionsListing()
    {
        try {
            $versions = $this->appVersion->get();
            $response = [];
            $isForceUpdate = false;
            foreach ($versions as $version) {
                $response[$version->platform] = [
                    'latest_version' => $version->latest_version,
                    'min_supported_version' => $version->min_supported_version,
                    'force_update' => $version->force_update,
                    'update_url' => $version->update_url,
                ];
                if ($version->force_update == true) {
                    $isForceUpdate = true;
                }
            }

            $message = $isForceUpdate == true ? 'A new version of Gofusion is available. Please update to continue.' : 'Fetch all versions successfully!';
            return $this->success(
                message: $message,
                result: $response,
                code: 200
            );
        } catch (\Throwable $th) {
            return $this->error(message: $th->getMessage());
        }
    }

    public function updatePlatform(string $platform, UpdateAppVersionRequest $request)
    {
        try {
            $platformVersion = $this->appVersion::platform($platform)->first();

            if (!$platformVersion) {
                return $this->error(
                    message: 'No record found against this platform',
                    code: 404
                );
            }

            // Use fill() with only validated data that is present
            $platformVersion->fill($request->only([
                'latest_version',
                'min_supported_version',
                'force_update',
                'update_url'
            ]));

            $platformVersion->save();

            return $this->success(
                message: ucfirst($platform) . ' app version updated successfully!',
                result: $platformVersion,
                code: 200
            );
        } catch (\Throwable $th) {
            return $this->error(
                message: $th->getMessage()
            );
        }
    }
}
