<?php
namespace CoasterCommerce\Core\Controllers\Admin;

use CoasterCommerce\Core\Menu\AdminMenu;
use CoasterCommerce\Core\Model\PDFSetting;
use CoasterCommerce\Core\Validation\ValidatesInput;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\ValidationException;

class PDFController extends AbstractController
{
    use ValidatesInput;

    /**
     *
     */
    protected function _init()
    {
        /** @var AdminMenu $adminMenu */
        $adminMenu = app('coaster-commerce.admin-menu');
        $adminMenu->getByName('Settings')->setActive();
    }

    /**
     * @return View
     */
    public function editSettings()
    {
        $this->_setTitle('PDF Details');
        return $this->_view('pdf.settings', [
            'settings' => PDFSetting::all(),
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse
     * @throws ValidationException
     */
    public function updateSettings(Request $request)
    {
        $inputData = $request->post();
        foreach ($inputData as $setting => $value) {
            if ($pdfSetting = (new PDFSetting)->where('setting', $setting)->first()) {
                $pdfSetting->value = $value;
                $pdfSetting->save();
            }
        }
        $inputData = $request->allFiles();
        $this->validate($inputData, array_fill_keys(array_keys($inputData), 'mimes:png,jpeg,gif'));
        foreach ($inputData as $setting => $file) {
            if ($pdfSetting = (new PDFSetting)->where('setting', $setting)->first()) {
                if ($pdfSetting->type == 'file-standard') {
                    /** @var UploadedFile $file */
                    $storeDir = 'pdf-images';
                    $storeDirPath = public_path($storeDir);
                    if (!file_exists($storeDirPath)) {
                        mkdir($storeDirPath);
                    }
                    $file->move($storeDirPath, $file->getClientOriginalName());
                    $pdfSetting->value = '/' . $storeDir . '/' . $file->getClientOriginalName();
                    $pdfSetting->save();
                }
            }
        }
        $this->_flashAlert('success', 'PDF settings updated!');
        return $this->_redirectRoute('system.pdf');
    }

}
