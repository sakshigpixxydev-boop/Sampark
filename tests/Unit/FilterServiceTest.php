<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\FilterService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FilterServiceTest extends TestCase
{
    protected FilterService $filterService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filterService = new FilterService();
        Session::flush();
    }

    public function test_save_filters_stores_filters_in_session()
    {
        $filters = ['status' => 'active', 'type' => 'student'];
        
        $this->filterService->saveFilters('test_key', $filters);
        
        $this->assertEquals($filters, Session::get('filters.test_key'));
    }

    public function test_save_filters_removes_empty_values()
    {
        $filters = ['status' => 'active', 'type' => '', 'name' => null];
        
        $this->filterService->saveFilters('test_key', $filters);
        
        $saved = Session::get('filters.test_key');
        $this->assertArrayHasKey('status', $saved);
        $this->assertArrayNotHasKey('type', $saved);
        $this->assertArrayNotHasKey('name', $saved);
    }

    public function test_get_filters_retrieves_filters_from_session()
    {
        $filters = ['status' => 'active'];
        Session::put('filters.test_key', $filters);
        
        $result = $this->filterService->getFilters('test_key');
        
        $this->assertEquals($filters, $result);
    }

    public function test_get_filters_returns_empty_array_when_no_filters()
    {
        $result = $this->filterService->getFilters('nonexistent_key');
        
        $this->assertEquals([], $result);
    }

    public function test_clear_filters_removes_filters_from_session()
    {
        Session::put('filters.test_key', ['status' => 'active']);
        
        $this->filterService->clearFilters('test_key');
        
        $this->assertNull(Session::get('filters.test_key'));
    }

    public function test_merge_filters_uses_request_filters_when_present()
    {
        Session::put('filters.test_key', ['old' => 'value']);
        
        $request = Request::create('/test', 'GET', ['status' => 'active', 'type' => 'student']);
        
        $result = $this->filterService->mergeFilters($request, 'test_key');
        
        $this->assertEquals(['status' => 'active', 'type' => 'student'], $result);
    }

    public function test_merge_filters_uses_session_filters_when_no_request_filters()
    {
        $sessionFilters = ['status' => 'active'];
        Session::put('filters.test_key', $sessionFilters);
        
        $request = Request::create('/test', 'GET');
        
        $result = $this->filterService->mergeFilters($request, 'test_key');
        
        $this->assertEquals($sessionFilters, $result);
    }

    public function test_get_active_filter_count_returns_correct_count()
    {
        $filters = ['status' => 'active', 'type' => 'student', 'empty' => '', 'null' => null];
        
        $count = $this->filterService->getActiveFilterCount($filters);
        
        $this->assertEquals(2, $count);
    }

    public function test_get_active_filter_count_returns_zero_for_empty_filters()
    {
        $count = $this->filterService->getActiveFilterCount([]);
        
        $this->assertEquals(0, $count);
    }
}
