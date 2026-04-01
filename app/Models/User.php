<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Log;
use Kreait\Firebase\Factory;
use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use App\Traits\AppCommonFunction;
use App\Mail\CompanyAdmin\ResetPasswordMail;
use Spatie\Permission\Traits\HasPermissions;
use App\Observers\UserObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([UserObserver::class])]
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HasApiTokens, HasRoles, AppCommonFunction, HasPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'first_name',
        'last_name',
        'work_email',
        'username',
        'city',
        'dob',
        'image',
        'company_id',
        'company_department_id',
        'is_admin',
        'job_title',
        'fcm_token',
        'surname',
        'email_verified_at',
        'is_sub_admin',
        'invite_code',
        'invited_by',
        'join_token_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function joinToken()
    {
        return $this->belongsTo(CompanyJoinToken::class, 'join_token_id');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
    public function userDetails()
    {
        return $this->hasOne(UserDetail::class, 'user_id')->withDefault();
    }

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_id')->withDefault();
    }

    public function companies()
    {
        return $this->belongsToMany(Company::class, 'user_companies')
            ->withPivot('company_department_id')
            ->withTimestamps();
    }

    public function department()
    {
        return $this->belongsTo(CompanyDepartment::class, 'company_department_id');
    }

    public function modes()
    {
        return $this->belongsToMany(Mode::class, 'user_modes', 'user_id', 'mode_id');
    }

    public function sendPasswordResetNotification($token): void
    {
        $url = route('company_admin.password.reset', ['token' => $token, 'email' => $this->email]);
        $this->sendEmail(user: $this, email: new ResetPasswordMail($this, $url));
    }

    public function getFullNameAttribute(): string
    {
        return ucfirst($this->first_name) . ' ' . ucfirst($this->last_name);
    }

    public function challenges()
    {
        return $this->belongsToMany(ChallengeStep::class, 'user_id');
    }

    public function userCompleteSessions()
    {
        return $this->hasMany(CompleteGoSessionUser::class, 'user_id');
    }
    public function progresses()
    {
        return $this->hasMany(GoUserProgress::class);
    }

    public function quiz_attempts()
    {
        return $this->hasMany(QuizAttempt::class, 'user_id');
    }

    public function image_attempts()
    {
        return $this->hasMany(ImageSubmissionStep::class, 'user_id');
    }

    public function appealing_attempts()
    {
        return $this->hasMany(ImageSubmissionStep::class, 'user_id')
                    ->where('status', 'appealing');
    }

    public function event_attempts()
    {
        return $this->hasMany(EventSubmissionStep::class, 'user_id');
    }

    public function spinwheel_attempts()
    {
        return $this->hasMany(SpinWheelSubmissionStep::class, 'user_id');
    }

    public function survey_feedback_attempts()
    {
        return $this->hasMany(SurvayFeedbackAttempt::class, 'user_id');
    }

    public function challenge_attempts()
    {
        return $this->hasMany(ChallengeStep::class, 'user_id');
    }

    public function isCitizen()
    {
        return $this->modes->contains(function ($mode){
            return $mode->name == 'Citizen';
        });
    }

    public function isEmployee()
    {
        return $this->modes->contains(function ($mode) {
            return $mode->name !== 'Citizen';
        });
    }

    public function scopeWhereRole($query, $role)
    {
        return $query->whereHas('roles', function ($q) use ($role) {
            $q->where('name', $role);
        });
    }

    public function transactions()
    {
        return $this->hasMany(UserTransaction::class, 'user_id');
    }

    public function sendNotification($title, $message,$data = [])
    {
        $serviceAccountPath = __DIR__ . '/../../gofusion-firebase.json';

        $factory = (new Factory)
            ->withServiceAccount($serviceAccountPath);

        $messaging = $factory->createMessaging();

        $notification = Notification::create($title, $message);

        $message = CloudMessage::withTarget('token', $this->fcm_token)->withNotification($notification)->withData($data);

        try {
            $messaging->send($message);
            return true;
        } catch (\Throwable $e) {
            Log::info("Notification Exception!".$e->getMessage());
        }
    }
    public function galleryImages()
    {
        return $this->hasMany(GalleryImages::class, 'user_id');
    }

    public function getRoleAttribute()
    {
        return $this->roles->first();
    }

    public function posts()
    {
        return $this->morphMany(Post::class, 'author');
    }
    public function getRegisterationDateAttribute(): string
    {
        return $this->created_at->format('d-m-Y');
    }

    public function leaves()
    {
        return $this->hasMany(UserLeaveTransaction::class, 'user_id');
    }

}
