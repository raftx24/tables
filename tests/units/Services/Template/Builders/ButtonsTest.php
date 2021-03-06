<?php

namespace LaravelEnso\Tables\Tests\units\Services\Template\Builders;

use Route;
use Mockery;
use App\User;
use Tests\TestCase;
use LaravelEnso\Helpers\app\Classes\Obj;
use LaravelEnso\Tables\app\Services\Template\Builders\Buttons;

class ButtonsTest extends TestCase
{
    private $meta;
    private $template;

    protected function setUp() :void
    {
        parent::setUp();

        // $this->withoutExceptionHandling();

        $this->meta = new Obj([]);

        $this->template = new Obj([
            'auth' => false,
            'buttons' => [],
        ]);
    }

    /** @test */
    public function can_build_with_type()
    {
        $this->template->get('buttons')->push(new Obj(['type' => 'row']));

        $this->build();

        $this->assertEquals(1, $this->template->get('buttons')->get('row')->count());

        $this->assertEquals(0, $this->template->get('buttons')->get('global')->count());

        $this->assertTrue($this->template->get('actions'));
    }

    /** @test */
    public function cannot_build_when_user_cannot_access_to_route()
    {
        $user = Mockery::mock(User::class)->makePartial();

        $this->actingAs($user);

        $this->template->get('buttons')->push(new Obj([
                'action' => '',
                'type' => 'row',
                'fullRoute' => 'test',
        ]));

        $this->template->get('buttons')->push('create');

        $this->template->set('auth', true);

        $user->shouldReceive('cannot')->andReturn(true);

        $this->build();

        $this->assertEmpty($this->template->get('buttons')->get('global'));

        $this->assertEmpty($this->template->get('buttons')->get('row'));
    }

    /** @test */
    public function can_build_with_route()
    {
        Route::any('test')->name('test');

        Route::getRoutes()->refreshNameLookups();

        $this->template->get('buttons')->push(new Obj([
            'action' => 'ajax',
            'type' => 'row',
            'fullRoute' => 'test',
        ]));

        $this->build();

        $this->assertEquals(
            '/test?dtRowId',
            $this->template->get('buttons')
                ->get('row')
                ->first()
                ->get('path')
        );
    }

    /** @test */
    public function can_build_with_predefined_buttons()
    {
        $this->template->set('buttons', collect(['create', 'show']));

        $this->build();

        $this->assertEquals(
            (new Obj(config('enso.tables.buttons.global.create')))->except('routeSuffix'),
            $this->template->get('buttons')->get('global')->first()->except('route')
        );

        $this->assertEquals(
            (new Obj(config('enso.tables.buttons.row.show')))->except('routeSuffix'),
            $this->template->get('buttons')->get('row')->first()->except('route')
        );

        $this->assertEquals(
            '.'.config('enso.tables.buttons.global.create.routeSuffix'),
            $this->template->get('buttons')->get('global')->first()->get('route')
        );

        $this->assertEquals(
            '.'.config('enso.tables.buttons.row.show.routeSuffix'),
            $this->template->get('buttons')->get('row')->first()->get('route')
        );
    }

    private function build(): void
    {
        (new Buttons(
            $this->template,
            $this->meta
        ))->build();
    }
}
