
/**
 * BEGIN: Subcontractor aliases for Writer/Article (non-breaking)
 * Map new URIs and names to existing controllers.
 */
Route::group(['middleware' => ['auth']], function () {

    // Admin area
    Route::prefix('admin')->group(function () {
        // Jobs (alias of Articles)
        Route::get('subcontractor-management/jobs', 'AdminArticleController@index')->name('admin.subcontractor.jobs');
        Route::get('subcontractor-management/jobs/create', 'AdminArticleController@create')->name('admin.subcontractor.jobs.create');
        Route::post('subcontractor-management/jobs', 'AdminArticleController@store')->name('admin.subcontractor.jobs.store');
        Route::get('subcontractor-management/jobs/{id}', 'AdminArticleController@show')->name('admin.subcontractor.jobs.show');
        Route::put('subcontractor-management/jobs/{id}', 'AdminArticleController@update')->name('admin.subcontractor.jobs.update');
        Route::delete('subcontractor-management/jobs/{id}', 'AdminArticleController@destroy')->name('admin.subcontractor.jobs.destroy');

        // Subcontractors (alias of Writers)
        Route::get('subcontractor-management/subcontractors', 'AdminArticleController@writers')->name('admin.subcontractor.subcontractors');
        // Rates & invoices can also be aliased if needed
        Route::get('subcontractor-management/rates', 'WriterRateController@index')->name('admin.subcontractor.rates');
        Route::get('subcontractor-management/invoices', 'AdminInvoiceController@index')->name('admin.subcontractor.invoices');

        // SOP => Job SOPs
        Route::get('subcontractor-management/sop', 'AdminSopController@index')->name('admin.subcontractor.sop');
    });

    // Member area
    Route::prefix('member')->group(function () {
        // Jobs (alias of Articles)
        Route::get('subcontractor-management/jobs', 'ArticleController@index')->name('member.subcontractor.jobs');
        // Subcontractor self-service areas can be mapped similarly
    });

});
/** END: Subcontractor aliases */
