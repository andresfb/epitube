<?php

declare(strict_types=1);

namespace App\Console\Commands\Tube;

use App\Models\Tube\Content;
use App\Models\Tube\Tag;
use App\Services\Tube\MergeTagsService;
use Illuminate\Console\Command;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Throwable;

use function Laravel\Prompts\clear;
use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;
use function Laravel\Prompts\intro;
use function Laravel\Prompts\outro;
use function Laravel\Prompts\search;
use function Laravel\Prompts\warning;

final class MergeTagsCommand extends Command
{
    protected $signature = 'merge:tags';

    protected $description = 'Move all Content from a source tag to a destination tag, then delete the source';

    public function handle(MergeTagsService $service): int
    {
        try {
            clear();
            intro('Merge Tags');

            $sourceId = (int) search(
                label: 'Source tag',
                options: fn (string $value): array => $this->tagOptions($value),
                placeholder: 'Type to search tags…',
            );

            $destinationId = (int) search(
                label: 'Destination tag',
                options: fn (string $value): array => $this->tagOptions($value, $sourceId),
                placeholder: 'Type to search tags…',
                validate: fn (int|string $value): ?string => (int) $value === $sourceId
                    ? 'Destination must be different from the source tag.'
                    : null,
            );

            $source = Tag::query()
                ->findOrFail($sourceId);

            $destination = Tag::query()
                ->findOrFail($destinationId);

            $count = Content::query()
                ->withAnyTags([$source])
                ->count();

            warning(sprintf(
                'Move %d content record(s) from «%s» → «%s» and delete the source tag.',
                $count,
                $source->name,
                $destination->name,
            ));

            if (! confirm('Continue?', false)) {
                info('Cancelled.');

                return self::SUCCESS;
            }

            $moved = $service->setToScreen(true)
                ->execute($source, $destination);

            info(sprintf(
                'Moved %d content record(s) to «%s» and deleted «%s».',
                $moved,
                $destination->name,
                $source->name,
            ));

            return self::SUCCESS;
        } catch (Throwable $e) {
            error($e->getMessage());

            return self::FAILURE;
        } finally {
            $this->newLine();
            outro('Done');
        }
    }

    /**
     * @return array<int, string>
     */
    private function tagOptions(string $value, ?int $excludeId = null): array
    {
        return Tag::query()
            ->select('tags.id', 'tags.slug', 'tags.name', DB::raw('COUNT(taggables.taggable_id) as contents_count'))
            ->leftJoin('taggables', function (JoinClause $join): void {
                $join->on('tags.id', '=', 'taggables.tag_id')
                    ->where('taggables.taggable_type', Content::class);
            })
            ->when($excludeId !== null, fn ($query) => $query->where('tags.id', '!=', $excludeId))
            ->groupBy('tags.id')
            ->orderBy('name')
            ->get()
            ->when(filled($value), fn (Collection $tags): Collection => $tags->filter(
                fn (Tag $tag): bool => str($tag->name)->contains($value, true)
                    || str($tag->slug)->contains($value, true)
            ))
            ->mapWithKeys(fn (Tag $tag): array => [
                $tag->id => sprintf('%s (%d) [%s]', $tag->name, $tag->contents_count, $tag->slug),
            ])
            ->all();
    }
}
