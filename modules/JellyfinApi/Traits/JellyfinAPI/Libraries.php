<?php

namespace Modules\JellyfinApi\Traits\JellyfinAPI;

use Exception;
use Psr\Http\Message\StreamInterface;

trait Libraries
{
    /**
     * @throws Exception
     */
    public function getMediaFolders(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Library/MediaFolders";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function deleteItemsFromLibraryAndFilesystem(array $ids): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Items";

        $this->setRequestQuery('ids', $ids);

        $this->verb = 'delete';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function deleteItemFromLibraryAndFilesystem(string $itemId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Items/$itemId";

        $this->verb = 'delete';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getAlbumSimilarItems(string $itemId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Albums/$itemId/Similar";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getArtistSimilarItems(string $itemId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Artists/$itemId/Similar";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getAllParentsOfAnItems(string $itemId, string $userId = null): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Items/$itemId/Ancestors";

        if (isset($userId)) {
            $this->setRequestQuery('userId', $userId);
        }

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function downloadsItemMedia(string $itemId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Items/$itemId/Download";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getOriginalFile(string $itemId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Items/$itemId/File";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getSimilarItems(string $itemId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Items/$itemId/Similar";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getThemeSongsAndVideos(string $itemId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Items/$itemId/ThemeMedia";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getThemeSongs(string $itemId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Items/$itemId/ThemeSongs";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getThemeVideos(string $itemId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Items/$itemId/ThemeVideos";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getItemCounts(string $userId = null, bool|null $isFavorite = null): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Items/Counts";

        if (isset($userId)) {
            $this->setRequestQuery('userId', $userId);
        }

        if (isset($isFavorite)) {
            $this->setRequestQuery('isFavorite', $isFavorite);
        }

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getLibraryOptionsInfo(string $libraryContentType = null, bool $isNewLibrary = false): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Items/Counts";

        if (isset($libraryContentType)) {
            $this->setRequestQuery('libraryContentType', $libraryContentType);
        }

        $this->setRequestQuery('isNewLibrary', $isNewLibrary);

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getAllUserMediaFolders(bool|null $isHidden = null): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Library/MediaFolders";

        if (isset($isHidden)) {
            $this->setRequestQuery('isHidden', $isHidden);
        }

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function startLibraryScan(): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Library/Refresh";

        $this->verb = 'post';

        return $this->doJellyfinRequest(false);
    }

    /**
     * @throws Exception
     */
    public function getMovieSimilarItems(string $itemId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Movies/$itemId/Similar";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getTVSimilarItems(string $itemId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Shows/$itemId/Similar";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }

    /**
     * @throws Exception
     */
    public function getTrailerSimilarItems(string $itemId): StreamInterface|array|string
    {
        $this->apiBaseUrl = $this->config['server_api_url'];

        $this->apiEndPoint = "Trailers/$itemId/Similar";

        $this->verb = 'get';

        return $this->doJellyfinRequest();
    }
}
