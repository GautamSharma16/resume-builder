<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Controllers\SitemapController;

class SeoConfigurationTest extends TestCase
{
    private array $indexableRoutes = [
        'home',
        'enhance-cv',
        'improve-cv',
        'cover-letter',
        'templates',
        'interview',
        'blog.show',
        'resources',
        'contact',
        'privacy',
        'terms',
        'plans',
        'resume.create',
        'resume-maker',
    ];

    public function test_target_routes_resolve_correctly_and_are_indexable(): void
    {
        $testCases = [
            '/improve-cv' => 'improve-cv',
            '/enhance-cv' => 'enhance-cv',
            '/resume-maker' => 'resume-maker',
            '/resume-maker/ats' => 'resume-maker',
            '/resume-maker/experienced' => 'resume-maker',
            '/resume-maker/fresher' => 'resume-maker',
            '/interview' => 'interview',
            '/privacy-policy' => 'privacy',
            '/terms-of-use' => 'terms',
            '/templates' => 'templates',
            '/contact' => 'contact',
            '/plans' => 'plans',
        ];

        foreach ($testCases as $uri => $expectedRouteName) {
            $request = Request::create($uri, 'GET');
            $route = app('router')->getRoutes()->match($request);
            
            $this->assertNotNull($route, "Route for {$uri} should exist");
            $this->assertEquals($expectedRouteName, $route->getName(), "Route name for {$uri} mismatch");
            
            $isIndexable = collect($this->indexableRoutes)->contains(fn ($p) => Str::is($p, $route->getName()));
            $this->assertTrue($isIndexable, "Route {$route->getName()} for URI {$uri} should be marked indexable");
        }
    }

    public function test_non_indexable_routes_are_excluded(): void
    {
        $excludedUris = [
            '/login',
            '/register',
            '/verify-otp',
        ];

        foreach ($excludedUris as $uri) {
            $request = Request::create($uri, 'GET');
            $route = app('router')->getRoutes()->match($request);
            
            $isIndexable = collect($this->indexableRoutes)->contains(fn ($p) => Str::is($p, $route->getName()));
            $this->assertFalse($isIndexable, "Route {$route->getName()} for URI {$uri} should NOT be indexable");
        }
    }

    public function test_robots_txt_disallows_login_and_private_areas(): void
    {
        $robotsContent = file_get_contents(public_path('robots.txt'));
        
        $this->assertStringContainsString('Disallow: /login', $robotsContent);
        $this->assertStringContainsString('Disallow: /admin', $robotsContent);
        $this->assertStringContainsString('Disallow: /dashboard', $robotsContent);
        $this->assertStringContainsString('Disallow: /profile', $robotsContent);
        $this->assertStringContainsString('Sitemap: https://cvbliss.in/sitemap.xml', $robotsContent);
    }

    public function test_privacy_and_terms_title_tags_are_optimized(): void
    {
        $privacyBlade = file_get_contents(resource_path('views/pages/privacy.blade.php'));
        $termsBlade = file_get_contents(resource_path('views/pages/terms.blade.php'));

        $this->assertStringContainsString("Privacy Policy & User Data Protection | CvBliss", $privacyBlade);
        $this->assertStringContainsString("Terms of Use & Service Agreement | CvBliss", $termsBlade);
    }
}
