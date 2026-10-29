<?php

namespace App\Support;

use App\Models\HouseholdMonitoringCase;
use App\Models\ResidentRequest;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class BhwHouseholdEditor
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public static function apply(ResidentRequest $resident, string $action, array $payload): string
    {
        return match ($action) {
            'add_child', 'update_child' => self::upsertChild($resident, $action, $payload),
            'remove_child' => self::removeChild($resident, $payload),
            'add_senior', 'update_senior' => self::upsertSenior($resident, $action, $payload),
            'remove_senior' => self::removeSenior($resident, $payload),
            'set_spouse' => self::setSpouse($resident, $payload),
            'remove_spouse' => self::removeSpouse($resident),
            default => throw ValidationException::withMessages([
                'household_action' => 'Unknown household update action.',
            ]),
        };
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function upsertChild(ResidentRequest $resident, string $action, array $payload): string
    {
        $children = self::normalizedList($resident->household_children);
        $index = self::optionalIndex($payload['member_index'] ?? null);
        $row = self::buildChildRow($payload);

        if ($action === 'update_child') {
            if ($index === null || ! array_key_exists($index, $children)) {
                throw ValidationException::withMessages([
                    'member_index' => 'Unable to update this child.',
                ]);
            }

            $oldName = self::memberName($children[$index]);
            $children[$index] = $row;
            $resident->update(['household_children' => array_values($children)]);
            self::reindexMonitoring($resident, 'household_child', self::normalizedList($resident->fresh()->household_children));
            if ($oldName !== '' && strcasecmp($oldName, $row['full_name']) !== 0) {
                self::renameMonitoring($resident, 'household_child', $oldName, $row['full_name']);
            }

            return 'Child details updated.';
        }

        $children[] = $row;
        $resident->update(['household_children' => array_values($children)]);

        return 'Child added to the household.';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function removeChild(ResidentRequest $resident, array $payload): string
    {
        $children = self::normalizedList($resident->household_children);
        $index = self::optionalIndex($payload['member_index'] ?? null);

        if ($index === null || ! array_key_exists($index, $children)) {
            throw ValidationException::withMessages([
                'member_index' => 'Unable to remove this child.',
            ]);
        }

        $removedName = self::memberName($children[$index]);
        unset($children[$index]);
        $children = array_values($children);
        $resident->update(['household_children' => $children !== [] ? $children : null]);
        self::completeMonitoringForMember($resident, 'household_child', $removedName);
        self::reindexMonitoring($resident, 'household_child', $children);

        return 'Child removed from the household.';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function upsertSenior(ResidentRequest $resident, string $action, array $payload): string
    {
        [$otherSeniors, $autoSeniors] = self::splitOtherAndAutoSeniors(self::normalizedList($resident->household_seniors));
        $index = self::optionalIndex($payload['member_index'] ?? null);
        $row = self::buildSeniorRow($payload);

        if ($action === 'update_senior') {
            if ($index === null || ! array_key_exists($index, $otherSeniors)) {
                throw ValidationException::withMessages([
                    'member_index' => 'Unable to update this family member.',
                ]);
            }

            $oldName = self::memberName($otherSeniors[$index]);
            $otherSeniors[$index] = $row;
            $merged = array_values(array_merge($otherSeniors, $autoSeniors));
            $resident->update(['household_seniors' => $merged]);
            self::reindexMonitoring($resident, 'household_senior', self::normalizedList($resident->fresh()->household_seniors));
            if ($oldName !== '' && strcasecmp($oldName, $row['full_name']) !== 0) {
                self::renameMonitoring($resident, 'household_senior', $oldName, $row['full_name']);
            }

            return 'Other family member details updated.';
        }

        $otherSeniors[] = $row;
        $merged = array_values(array_merge($otherSeniors, $autoSeniors));
        $resident->update(['household_seniors' => $merged]);

        return 'Other family member added to the household.';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function removeSenior(ResidentRequest $resident, array $payload): string
    {
        [$otherSeniors, $autoSeniors] = self::splitOtherAndAutoSeniors(self::normalizedList($resident->household_seniors));
        $index = self::optionalIndex($payload['member_index'] ?? null);

        if ($index === null || ! array_key_exists($index, $otherSeniors)) {
            throw ValidationException::withMessages([
                'member_index' => 'Unable to remove this family member.',
            ]);
        }

        $removedName = self::memberName($otherSeniors[$index]);
        unset($otherSeniors[$index]);
        $otherSeniors = array_values($otherSeniors);
        $merged = array_values(array_merge($otherSeniors, $autoSeniors));
        $resident->update(['household_seniors' => $merged !== [] ? $merged : null]);
        self::completeMonitoringForMember($resident, 'household_senior', $removedName);
        self::reindexMonitoring($resident, 'household_senior', $merged);

        return 'Other family member removed from the household.';
    }

    /**
     * @param  list<array<string, mixed>>  $seniors
     * @return array{0: list<array<string, mixed>>, 1: list<array<string, mixed>>}
     */
    private static function splitOtherAndAutoSeniors(array $seniors): array
    {
        $other = [];
        $auto = [];

        foreach ($seniors as $row) {
            $source = trim((string) ($row['source'] ?? 'other'));
            if (in_array($source, ['guardian', 'spouse'], true)) {
                $auto[] = $row;
            } else {
                $other[] = $row;
            }
        }

        return [$other, $auto];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function setSpouse(ResidentRequest $resident, array $payload): string
    {
        $firstName = trim((string) ($payload['spouse_first_name'] ?? ''));
        $lastName = trim((string) ($payload['spouse_last_name'] ?? ''));
        $middleInitial = strtoupper(trim((string) ($payload['spouse_middle_initial'] ?? '')));
        $middleSegment = $middleInitial !== '' ? $middleInitial.'.' : null;
        $fullName = trim(implode(' ', array_filter([$firstName, $middleSegment, $lastName])));
        $birthdate = trim((string) ($payload['spouse_birthdate'] ?? ''));
        $parentRole = trim((string) ($payload['spouse_parent_role'] ?? ''));
        $placeOfBirth = trim((string) ($payload['spouse_place_of_birth'] ?? ''));
        $educationalAttainment = trim((string) ($payload['spouse_educational_attainment'] ?? ''));

        if ($firstName === '' || $lastName === '') {
            throw ValidationException::withMessages([
                'spouse_first_name' => 'Please enter the spouse first and last name.',
            ]);
        }

        if ($birthdate === '') {
            throw ValidationException::withMessages([
                'spouse_birthdate' => 'Please enter the spouse birthdate.',
            ]);
        }

        if (! in_array($parentRole, ResidentRequest::SPOUSE_PARENT_ROLES, true)) {
            throw ValidationException::withMessages([
                'spouse_parent_role' => 'Please select the spouse guardian role.',
            ]);
        }

        $residentRole = trim((string) ($resident->parent_role ?? ''));
        if ($residentRole !== '' && $parentRole === $residentRole) {
            throw ValidationException::withMessages([
                'spouse_parent_role' => 'Spouse guardian role must be different from the resident.',
            ]);
        }

        if ($placeOfBirth === '') {
            throw ValidationException::withMessages([
                'spouse_place_of_birth' => 'Please enter the spouse place of birth.',
            ]);
        }

        if ($educationalAttainment === '' || ! in_array($educationalAttainment, ResidentRequest::EDUCATIONAL_ATTAINMENTS, true)) {
            throw ValidationException::withMessages([
                'spouse_educational_attainment' => 'Please select educational attainment.',
            ]);
        }

        $gender = ResidentRequest::genderFromParentRole($parentRole) ?? 'Male';
        $oldName = trim((string) ($resident->spouse_full_name ?? ''));
        $details = [
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_initial' => $middleInitial !== '' ? $middleInitial : null,
            'full_name' => $fullName,
            'birthdate' => $birthdate,
            'parent_role' => $parentRole,
            'gender' => $gender,
            'place_of_birth' => $placeOfBirth,
            'educational_attainment' => $educationalAttainment,
        ];

        $resident->update([
            'spouse_full_name' => $fullName,
            'spouse_details' => $details,
        ]);

        if ($resident->user) {
            $resident->user->update([
                'spouse_full_name' => $fullName,
                'spouse_details' => $details,
            ]);
        }

        if ($oldName !== '' && strcasecmp($oldName, $fullName) !== 0) {
            self::renameMonitoring($resident, 'spouse', $oldName, $fullName);
        }

        return $oldName !== '' ? 'Spouse details updated.' : 'Spouse added to the household.';
    }

    private static function removeSpouse(ResidentRequest $resident): string
    {
        $oldName = trim((string) ($resident->spouse_full_name ?? ''));

        $resident->update([
            'spouse_full_name' => null,
            'spouse_details' => null,
        ]);

        if ($resident->user) {
            $resident->user->update([
                'spouse_full_name' => null,
                'spouse_details' => null,
            ]);
        }

        if ($oldName !== '') {
            self::completeMonitoringForMember($resident, 'spouse', $oldName);
        }

        return 'Spouse removed from the household.';
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private static function buildChildRow(array $payload): array
    {
        $fullName = trim((string) ($payload['full_name'] ?? ''));
        $birthdate = trim((string) ($payload['birthdate'] ?? ''));
        $placeOfBirth = trim((string) ($payload['place_of_birth'] ?? ''));
        $gender = ucfirst(strtolower(trim((string) ($payload['gender'] ?? ''))));
        $relationship = trim((string) ($payload['relationship'] ?? ''));
        $currentlyStudying = in_array($payload['currently_studying'] ?? '', ['yes', 'no', 'working', 'not_studying', 'not_working', 'infant'], true)
            ? (string) $payload['currently_studying']
            : '';

        if ($fullName === '') {
            throw ValidationException::withMessages(['full_name' => 'Please enter the full name.']);
        }
        if ($birthdate === '') {
            throw ValidationException::withMessages(['birthdate' => 'Please enter the birthdate.']);
        }
        if ($placeOfBirth === '') {
            throw ValidationException::withMessages(['place_of_birth' => 'Please enter the place of birth.']);
        }
        if (! in_array($gender, ['Male', 'Female'], true)) {
            throw ValidationException::withMessages(['gender' => 'Please select gender.']);
        }
        if (! in_array($relationship, ResidentRequest::CHILD_RELATIONSHIPS, true)) {
            throw ValidationException::withMessages(['relationship' => 'Please select relationship.']);
        }
        if ($currentlyStudying === '') {
            throw ValidationException::withMessages(['currently_studying' => 'Please select studying status.']);
        }

        try {
            if (Carbon::parse($birthdate)->startOfDay()->gt(now()->startOfDay())) {
                throw ValidationException::withMessages(['birthdate' => 'Birthdate cannot be in the future.']);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable) {
            throw ValidationException::withMessages(['birthdate' => 'Please enter a valid birthdate.']);
        }

        if ($currentlyStudying === 'infant') {
            return [
                'full_name' => $fullName,
                'birthdate' => $birthdate,
                'place_of_birth' => $placeOfBirth,
                'age_in_months' => PersonAge::ageInWholeMonths($birthdate),
                'gender' => $gender,
                'relationship' => $relationship,
                'currently_studying' => 'infant',
                'school' => null,
                'educational_attainment' => null,
                'occupation' => null,
            ];
        }

        if ($currentlyStudying === 'yes') {
            $school = trim((string) ($payload['school'] ?? ''));
            if ($school === '') {
                throw ValidationException::withMessages(['school' => 'Please enter the school.']);
            }

            return [
                'full_name' => $fullName,
                'birthdate' => $birthdate,
                'place_of_birth' => $placeOfBirth,
                'age_in_months' => PersonAge::ageInWholeMonths($birthdate),
                'gender' => $gender,
                'relationship' => $relationship,
                'currently_studying' => 'yes',
                'school' => $school,
                'educational_attainment' => null,
                'occupation' => null,
            ];
        }

        if (in_array($currentlyStudying, ['not_studying', 'not_working'], true)) {
            return [
                'full_name' => $fullName,
                'birthdate' => $birthdate,
                'place_of_birth' => $placeOfBirth,
                'age_in_months' => PersonAge::ageInWholeMonths($birthdate),
                'gender' => $gender,
                'relationship' => $relationship,
                'currently_studying' => $currentlyStudying,
                'school' => null,
                'educational_attainment' => null,
                'occupation' => null,
            ];
        }

        $educationalAttainment = trim((string) ($payload['educational_attainment'] ?? ''));
        $occupation = trim((string) ($payload['occupation'] ?? ''));
        if ($educationalAttainment === '' || ! in_array($educationalAttainment, ResidentRequest::EDUCATIONAL_ATTAINMENTS, true)) {
            throw ValidationException::withMessages(['educational_attainment' => 'Please select educational attainment.']);
        }
        if ($occupation === '') {
            throw ValidationException::withMessages(['occupation' => 'Please enter occupation.']);
        }

        return [
            'full_name' => $fullName,
            'birthdate' => $birthdate,
            'place_of_birth' => $placeOfBirth,
            'age_in_months' => PersonAge::ageInWholeMonths($birthdate),
            'gender' => $gender,
            'relationship' => $relationship,
            'currently_studying' => 'working',
            'school' => null,
            'educational_attainment' => $educationalAttainment,
            'occupation' => $occupation,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private static function buildSeniorRow(array $payload): array
    {
        $fullName = trim((string) ($payload['full_name'] ?? ''));
        $birthdate = trim((string) ($payload['birthdate'] ?? ''));
        $gender = ucfirst(strtolower(trim((string) ($payload['gender'] ?? ''))));
        $relationship = trim((string) ($payload['relationship'] ?? ''));
        $placeOfBirth = trim((string) ($payload['place_of_birth'] ?? ''));
        $educationalAttainment = trim((string) ($payload['educational_attainment'] ?? ''));
        $civilStatus = trim((string) ($payload['civil_status'] ?? ''));
        $occupation = trim((string) ($payload['occupation'] ?? ''));

        if ($fullName === '') {
            throw ValidationException::withMessages(['full_name' => 'Please enter the full name.']);
        }
        if ($birthdate === '') {
            throw ValidationException::withMessages(['birthdate' => 'Please enter the birthdate.']);
        }
        if (! in_array($gender, ['Male', 'Female'], true)) {
            throw ValidationException::withMessages(['gender' => 'Please select gender.']);
        }
        if (! in_array($relationship, ResidentRequest::HOUSEHOLD_SENIOR_RELATIONSHIPS, true)) {
            throw ValidationException::withMessages(['relationship' => 'Please select relationship.']);
        }
        if ($placeOfBirth === '') {
            throw ValidationException::withMessages(['place_of_birth' => 'Please enter place of birth.']);
        }
        if ($civilStatus === '' || ! in_array($civilStatus, User::CIVIL_STATUSES, true)) {
            throw ValidationException::withMessages(['civil_status' => 'Please select marital status.']);
        }
        if ($educationalAttainment === '' || ! in_array($educationalAttainment, ResidentRequest::EDUCATIONAL_ATTAINMENTS, true)) {
            throw ValidationException::withMessages(['educational_attainment' => 'Please select educational attainment.']);
        }

        $age = PersonAge::ageInYears($birthdate);
        if ($age === null) {
            throw ValidationException::withMessages([
                'birthdate' => 'Please enter a valid birthdate.',
            ]);
        }

        return [
            'full_name' => $fullName,
            'birthdate' => $birthdate,
            'age_in_years' => $age,
            'gender' => $gender,
            'relationship' => $relationship,
            'place_of_birth' => $placeOfBirth,
            'educational_attainment' => $educationalAttainment,
            'civil_status' => $civilStatus,
            'occupation' => $occupation !== '' ? $occupation : null,
            'is_elderly' => $age >= 60,
            'source' => 'other',
        ];
    }

    /**
     * @param  mixed  $list
     * @return list<array<string, mixed>>
     */
    private static function normalizedList(mixed $list): array
    {
        if (! is_array($list)) {
            return [];
        }

        $rows = [];
        foreach ($list as $row) {
            if (is_array($row) && self::memberName($row) !== '') {
                $rows[] = $row;
            }
        }

        return array_values($rows);
    }

    /**
     * @param  array<string, mixed>  $row
     */
    private static function memberName(array $row): string
    {
        return trim((string) ($row['full_name'] ?? $row['name'] ?? ''));
    }

    private static function optionalIndex(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (! is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private static function completeMonitoringForMember(ResidentRequest $resident, string $source, string $name): void
    {
        if ($name === '') {
            return;
        }

        HouseholdMonitoringCase::query()
            ->where('resident_request_id', $resident->id)
            ->where('is_active', true)
            ->where('member_source', $source)
            ->whereRaw('LOWER(TRIM(member_name)) = ?', [mb_strtolower(trim($name))])
            ->update([
                'is_active' => false,
                'completed_at' => now(),
            ]);
    }

    private static function renameMonitoring(ResidentRequest $resident, string $source, string $oldName, string $newName): void
    {
        if ($oldName === '' || $newName === '') {
            return;
        }

        HouseholdMonitoringCase::query()
            ->where('resident_request_id', $resident->id)
            ->where('member_source', $source)
            ->whereRaw('LOWER(TRIM(member_name)) = ?', [mb_strtolower(trim($oldName))])
            ->update(['member_name' => $newName]);
    }

    /**
     * @param  list<array<string, mixed>>  $members
     */
    private static function reindexMonitoring(ResidentRequest $resident, string $source, array $members): void
    {
        $nameToIndex = [];
        foreach ($members as $index => $row) {
            $name = mb_strtolower(self::memberName($row));
            if ($name !== '') {
                $nameToIndex[$name] = $index;
            }
        }

        $cases = HouseholdMonitoringCase::query()
            ->where('resident_request_id', $resident->id)
            ->where('member_source', $source)
            ->get();

        foreach ($cases as $case) {
            $key = mb_strtolower(trim((string) $case->member_name));
            if ($key === '' || ! array_key_exists($key, $nameToIndex)) {
                if ($case->is_active) {
                    $case->update([
                        'is_active' => false,
                        'completed_at' => $case->completed_at ?? now(),
                    ]);
                }

                continue;
            }

            $case->update(['member_index' => $nameToIndex[$key]]);
        }
    }
}
