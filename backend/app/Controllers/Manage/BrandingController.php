<?php

declare(strict_types=1);

namespace App\Controllers\Manage;

use App\Controllers\BaseController;
use App\Models\PropertyModel;

/** Lets a Body Corporate put their own logo/colour on the tenant app and notifications. */
class BrandingController extends BaseController
{
    public function edit()
    {
        $property = model(PropertyModel::class)->find(auth()->user()->property_id);

        return view('manage/branding/edit', [
            'title'      => 'Branding — Dropa Manage',
            'panelLabel' => 'Body Corporate',
            'nav'        => view('manage/_nav'),
            'property'   => $property,
        ]);
    }

    public function update()
    {
        $propertyId = auth()->user()->property_id;

        model(PropertyModel::class)->update($propertyId, [
            'logo_url'    => trim((string) $this->request->getPost('logo_url')) ?: null,
            'brand_color' => trim((string) $this->request->getPost('brand_color')) ?: null,
        ]);

        $this->audit('branding.update', 'property', (int) $propertyId);

        return redirect()->to('/manage/branding')->with('success', 'Branding updated.');
    }
}
