<script setup>
import * as Card from '@/Bits/Components/Card/Card.js';
import Gallery from '@/Bits/Components/Attachment/Gallery.vue';
import MediaButton from '@/Bits/Components/Buttons/MediaButton.vue';
import Asset from '@/utils/support/Asset';
import {computed, ref, watch} from 'vue';

const props = defineProps({
  product: Object,
  productEditModel: Object,
})

const isVideoActive = ref(false);

const getFirstImageUrl = (images) => {
  if (!Array.isArray(images)) {
    return '';
  }

  const firstImage = images.find((image) => image?.url);
  return firstImage?.url ?? '';
};

const previewImage = computed(() => {
  const galleryPreview = getFirstImageUrl(props.product?.gallery);
  if (galleryPreview) {
    return galleryPreview;
  }

  const detailGalleryPreview = getFirstImageUrl(props.product?.detail?.gallery_image?.meta_value);
  if (detailGalleryPreview) {
    return detailGalleryPreview;
  }

  const featuredMedia = props.product?.detail?.featured_media?.url
    ?? props.product?.featured_media
    ?? '';

  if (featuredMedia) {
    return featuredMedia;
  }

  return Asset.getUrl('images/placeholder.svg');
});

const withAutoplayParam = (url) => {
  try {
    const parsedUrl = new URL(url, window.location.origin);
    parsedUrl.searchParams.set('autoplay', '1');
    return parsedUrl.toString();
  } catch (error) {
    return url.includes('?') ? `${url}&autoplay=1` : `${url}?autoplay=1`;
  }
};

const normalizeFeaturedVideo = (video) => {
  const normalized = video && typeof video === 'object' ? {...video} : {};

  normalized.url = (normalized.url ?? '').trim();
  normalized.title = normalized.title ?? '';

  if (!normalized.url) {
    return {url: ''};
  }

  if (!normalized.type) {
    normalized.type = detectVideoType(normalized.url);
  }

  return normalized;
};

const updateFeaturedVideo = (video) => {
  const formattedVideo = normalizeFeaturedVideo(video);

  props.product.featured_video = formattedVideo;
  props.productEditModel.updateMedia('featured_video', formattedVideo);
}

const onVideoSelected = (selected) => {
  if (!Array.isArray(selected) || !selected.length) {
    updateFeaturedVideo({});
    return;
  }

  const file = selected[0];
  updateFeaturedVideo({
    id: file.id ?? '',
    title: file.title ?? '',
    url: file.url ?? '',
    type: 'file'
  });
}

const detectVideoType = (url) => {
  if (!url) {
    return 'url';
  }

  return /\.(mp4|mov|webm|ogg)(\?.*)?$/i.test(url) ? 'file' : 'url';
}

const updateVideoUrl = (value) => {
  const url = value ? value.trim() : '';

  const currentVideo = props.product.featured_video || {};

  updateFeaturedVideo({
    ...currentVideo,
    url,
    type: url ? detectVideoType(url) : ''
  });
}

const clearVideo = () => {
  isVideoActive.value = false;
  updateFeaturedVideo({});
}

const videoUrl = computed(() => props.product?.featured_video?.url ?? '');
const isFileSource = computed(() => {
  if (!videoUrl.value) {
    return false;
  }

  const type = props.product?.featured_video?.type;
  if (type === 'file') {
    return true;
  }

  return /\.(mp4|mov|webm|ogg)(\?.*)?$/i.test(videoUrl.value);
});

const iframeSrc = computed(() => {
  if (!videoUrl.value) {
    return '';
  }

  if (!isFileSource.value && isVideoActive.value) {
    return withAutoplayParam(videoUrl.value);
  }

  return videoUrl.value;
});

const activateVideo = () => {
  isVideoActive.value = true;
};

watch(videoUrl, () => {
  isVideoActive.value = false;
});

const videoAttachments = computed(() => {
  const video = props.product?.featured_video;
  if (video?.id && video.url) {
    return [{
      id: video.id,
      title: video.title ?? '',
      url: video.url
    }];
  }

  return [];
});
</script>

<template>
  <div class="fct-product-media-wrap">
    <Card.Container>
      <Card.Header :title="$t('Media')" border_bottom title_size="small"></Card.Header>
      <Card.Body>
        <div class="fct-admin-summary-item">
          <Gallery
            :attachments="product.gallery"
            :featured_image_id ="product.featured_image_id"
            @mediaUploaded="value => {
              product.gallery = value
              productEditModel.updateMedia('gallery',value);
              // if (productEditModel.hasChange){
              //     productEditModel.hideAdminProductMenuItems(true);
              // }
            }"
            @removeImage="value => {
              product.gallery.splice(value, 1)
              product.gallery = [...product.gallery];
              productEditModel.updateMedia('gallery',product.gallery);
              // productEditModel.setHasChange(true);
            }"
            @onMediaChange="value => {
              productEditModel.updateMedia('gallery',value);
            }"
          />
        </div>

        <div class="fct-admin-summary-item mt-4">
          <div class="flex items-start justify-between gap-3">
            <div>
              <p class="fct-admin-summary-item-title">{{ $t('Featured Video') }}</p>
              <p class="fct-admin-summary-item-desc">{{ $t('Use a product video as the featured media on the product page.') }}</p>
            </div>

            <el-button v-if="videoUrl" type="danger" text @click="clearVideo">
              {{ $t('Remove') }}
            </el-button>
          </div>

          <div class="fct-admin-input-wrapper mt-3">
            <el-form label-position="top">
              <el-form-item :label="$t('Video URL')">
                <el-input
                    :model-value="videoUrl"
                    :placeholder="$t('YouTube, Vimeo or direct MP4 URL')"
                    @input="updateVideoUrl"
                />
              </el-form-item>
            </el-form>
          </div>

          <div class="flex items-center gap-3 mt-2">
            <MediaButton
                icon="Video"
                :title="$t('Upload Video')"
                :attachments="videoAttachments"
                :library-type="'video'"
                @onMediaSelected="onVideoSelected"
            />
            <p class="text-xs text-gray-500">{{ $t('Upload a video file or paste a public video link.') }}</p>
          </div>

          <div class="mt-3" v-if="videoUrl">
            <div
                v-if="!isVideoActive"
                class="relative overflow-hidden rounded-lg border border-dashed border-gray-200 dark:border-dark-400 cursor-pointer"
                role="button"
                tabindex="0"
                @click="activateVideo"
                @keyup.enter.prevent="activateVideo"
                @keyup.space.prevent="activateVideo"
                :aria-label="$t('Play featured video preview')"
            >
              <img
                  :src="previewImage"
                  :alt="product.post_title || $t('Product media preview')"
                  class="w-full object-cover"
                  style="max-height: 340px;"
              />
              <div class="absolute inset-0 bg-black bg-opacity-40 flex items-center justify-center">
                <span class="flex items-center justify-center w-16 h-16 rounded-full bg-white bg-opacity-90 shadow-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24" fill="none">
                    <path d="M9 7L17 12L9 17V7Z" fill="#1F2937"/>
                  </svg>
                </span>
              </div>
            </div>
            <video
                v-else-if="isFileSource"
                :src="videoUrl"
                :poster="previewImage"
                :autoplay="isVideoActive"
                :muted="isVideoActive"
                controls
                playsinline
                style="width: 100%; border-radius: 8px; max-height: 340px;"
            ></video>
            <iframe
                v-else
                :src="iframeSrc"
                allowfullscreen
                allow="autoplay; encrypted-media"
                style="width: 100%; min-height: 260px; border: 0; border-radius: 8px;"
            ></iframe>
          </div>
        </div>
      </Card.Body>
    </Card.Container>
  </div>
</template>
