use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    protected $proxies = '*'; // or use [‘*’] for Laravel >= 10.6

    protected $headers = Request::HEADER_X_FORWARDED_ALL;
}
