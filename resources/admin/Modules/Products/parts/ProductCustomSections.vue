<script setup>
import * as Card from '@/Bits/Components/Card/Card.js';
import MediaButton from "@/Bits/Components/Buttons/MediaButton.vue";
import translate from "@/utils/translator/Translator";
import AppConfig from "@/utils/Config/AppConfig";
import {computed, ref, watch} from "vue";

const props = defineProps({
  product: Object,
  productEditModel: Object
})

const maxSections = 4;
const isPremiumLocked = computed(() => !AppConfig.get('app_config.isProActive'));

const preventPremiumAction = () => {
  return isPremiumLocked.value;
};

const getDefaultVideo = () => ({
  url: '',
  type: '',
  id: '',
  title: ''
});

const getDefaultSection = () => ({
  title: '',
  description: '',
  media_type: 'image',
  image: null,
  video: getDefaultVideo()
});

const detectVideoType = (url) => {
  if (!url) {
    return '';
  }

  return /\.(mp4|mov|webm|ogg)(\?.*)?$/i.test(url) ? 'file' : 'url';
}

const normalizeVideo = (video) => {
  if (!video || typeof video !== 'object') {
    return getDefaultVideo();
  }

  const normalized = {
    url: video.url?.trim() || '',
    type: video.type || '',
    id: video.id || '',
    title: video.title || ''
  };

  if (normalized.url && !normalized.type) {
    normalized.type = detectVideoType(normalized.url);
  }

  return normalized;
};

const normalizeSection = (section) => {
  if (!section || typeof section !== 'object') {
    return getDefaultSection();
  }

  const normalized = {
    ...getDefaultSection(),
    ...section
  };

  normalized.video = normalizeVideo(normalized.video);
  normalized.media_type = normalized.media_type || 'image';
  normalized.image = normalized.image || null;

  return normalized;
};

const normalizeSections = (sections) => {
  if (!Array.isArray(sections)) {
    return [getDefaultSection()];
  }

  const normalized = sections
      .map(normalizeSection)
      .slice(0, maxSections);

  return normalized.length ? normalized : [getDefaultSection()];
};

const sections = ref(normalizeSections(props.product?.detail?.other_info?.custom_sections));
const activeSection = ref('section-0');

watch(() => props.product?.detail?.other_info?.custom_sections, (value) => {
  sections.value = normalizeSections(value);
  ensureActiveSection();
}, {immediate: true});

const hasReachedLimit = computed(() => sections.value.length >= maxSections);

const persistSections = () => {
  if (preventPremiumAction()) {
    return;
  }

  props.productEditModel.onChangeInputField('custom_sections', sections.value);
};

const updateSectionField = (index, key, value) => {
  if (preventPremiumAction()) {
    return;
  }

  const updatedSections = [...sections.value];
  const section = normalizeSection(updatedSections[index]);
  section[key] = value;
  updatedSections.splice(index, 1, section);
  sections.value = updatedSections;
  persistSections();
};

const addSection = () => {
  if (preventPremiumAction() || hasReachedLimit.value) {
    return;
  }

  sections.value = [...sections.value, getDefaultSection()];
  persistSections();
  setActiveSection(sections.value.length - 1);
};

const removeSection = (index) => {
  if (preventPremiumAction()) {
    return;
  }

  const updated = [...sections.value];
  updated.splice(index, 1);
  sections.value = normalizeSections(updated);
  persistSections();
  ensureActiveSection();
};

const onImageSelected = (index, selected) => {
  if (preventPremiumAction()) {
    return;
  }

  const image = Array.isArray(selected) && selected.length
      ? {
        id: selected[0]?.id ?? '',
        title: selected[0]?.title ?? '',
        url: selected[0]?.url ?? ''
      }
      : null;

  updateSectionField(index, 'media_type', 'image');
  updateSectionField(index, 'image', image);
};

const clearImage = (index) => {
  if (preventPremiumAction()) {
    return;
  }

  updateSectionField(index, 'image', null);
};

const onVideoSelected = (index, selected) => {
  if (preventPremiumAction()) {
    return;
  }

  if (!Array.isArray(selected) || !selected.length) {
    updateSectionField(index, 'video', getDefaultVideo());
    return;
  }

  const file = selected[0];
  const video = {
    id: file.id ?? '',
    title: file.title ?? '',
    url: file.url ?? '',
    type: 'file'
  };

  updateSectionField(index, 'media_type', 'video');
  updateSectionField(index, 'video', video);
};

const updateVideoUrl = (index, value) => {
  if (preventPremiumAction()) {
    return;
  }

  const url = value ? value.trim() : '';
  const current = normalizeVideo(sections.value[index]?.video);

  current.url = url;
  current.type = url ? detectVideoType(url) : '';

  updateSectionField(index, 'media_type', 'video');
  updateSectionField(index, 'video', current);
};

const clearVideo = (index) => {
  if (preventPremiumAction()) {
    return;
  }

  updateSectionField(index, 'video', getDefaultVideo());
};

const getVideoAttachments = (section) => {
  return section.video?.id && section.video?.url ? [{
    id: section.video.id,
    title: section.video.title ?? '',
    url: section.video.url
  }] : [];
};

const getVideoLabel = (section) => {
  if (section.video?.title) {
    return section.video.title;
  }

  if (section.title) {
    return section.title;
  }

  return translate('Selected video');
};

const hasVideoUrl = (section) => !!section?.video?.url;

const setActiveSection = (index) => {
  activeSection.value = `section-${index}`;
};

const ensureActiveSection = () => {
  if (!sections.value.length) {
    activeSection.value = '';
    return;
  }

  const currentIndex = sections.value.findIndex((section, idx) => `section-${idx}` === activeSection.value);

  if (currentIndex === -1) {
    activeSection.value = 'section-0';
  }
};

</script>

<template>
  <div class="fct-product-custom-section-wrap">
    <Card.Container>
      <Card.Header
          :title="translate('Custom Sections')"
          :desc="translate('Add up to four sections with a heading, short description, and supporting image or video.')"
          border_bottom
      />
      <Card.Body>
        <div class="fct-premium-gated" :class="{'is-locked': isPremiumLocked}" tabindex="0">
          <el-collapse v-model="activeSection" accordion class="fct-custom-sections">
            <el-collapse-item
                v-for="(section, index) in sections"
                :key="`section-${index}`"
                :name="`section-${index}`"
                class="fct-custom-section"
                :disabled="isPremiumLocked"
            >
            <template #title>
              <div class="fct-custom-section__header">
                <p class="fct-custom-section__title">
                  {{ translate('Section') }} {{ index + 1 }}
                </p>
                <el-button
                    v-if="sections.length > 1"
                    text
                    type="danger"
                    size="small"
                    @click.stop="removeSection(index)"
                    :disabled="isPremiumLocked"
                >
                  {{ translate('Remove') }}
                </el-button>
              </div>
            </template>

              <div class="fct-custom-section__body">
                <el-form label-position="top" require-asterisk-position="right">
                  <div class="fct-admin-input-wrapper">
                    <el-form-item :label="translate('Heading')">
                      <el-input
                          :placeholder="translate('Add heading')"
                          :model-value="section.title"
                          @input="value => updateSectionField(index, 'title', value)"
                          :disabled="isPremiumLocked"
                      />
                    </el-form-item>
                  </div>

                  <div class="fct-admin-input-wrapper">
                    <el-form-item :label="translate('Short description')">
                      <el-input
                          type="textarea"
                          :rows="2"
                          :placeholder="translate('Add a concise description')"
                          :model-value="section.description"
                          @input="value => updateSectionField(index, 'description', value)"
                          :disabled="isPremiumLocked"
                      />
                    </el-form-item>
                  </div>

                  <div class="fct-admin-input-wrapper">
                    <el-form-item :label="translate('Media type')">
                      <el-radio-group
                          :model-value="section.media_type"
                          @change="value => updateSectionField(index, 'media_type', value)"
                          :disabled="isPremiumLocked"
                      >
                        <el-radio-button label="image">{{ translate('Image') }}</el-radio-button>
                        <el-radio-button label="video">{{ translate('Video') }}</el-radio-button>
                    </el-radio-group>
                  </el-form-item>
                </div>

                <div v-if="section.media_type === 'image'" class="fct-admin-input-wrapper">
                    <el-form-item :label="translate('Section image')">
                      <div class="fct-section-media-picker">
                        <div v-if="section.image?.url" class="fct-section-attachment">
                          <div class="fct-section-attachment__thumb">
                            <img :src="section.image.url" :alt="section.image.title || section.title || translate('Section image')">
                          </div>
                          <div class="fct-section-attachment__info">
                            <p class="fct-section-attachment__title">{{ section.image.title || section.title || translate('Selected image') }}</p>
                            <span class="fct-section-media-meta">{{ translate('Image attachment') }}</span>
                          </div>
                          <div class="fct-section-attachment__actions">
                            <el-button
                                text
                                type="danger"
                                size="small"
                                @click="clearImage(index)"
                                :disabled="isPremiumLocked"
                            >
                              {{ translate('Remove') }}
                            </el-button>
                        </div>
                      </div>

                      <div class="fct-section-media-actions">
                          <MediaButton
                              icon="Picture"
                              :title="section.image ? translate('Change image') : translate('Select image')"
                              :attachments="section.image ? [section.image] : []"
                              :multiple="false"
                              @onMediaSelected="selected => onImageSelected(index, selected)"
                              :class="{'fct-pointer-none': isPremiumLocked}"
                          />
                        </div>
                      </div>
                  </el-form-item>
                </div>

                <div v-else class="fct-admin-input-wrapper">
                  <el-form-item :label="translate('Section video')">
                    <div class="fct-section-media-picker">
                        <div class="fct-section-media-actions">
                          <el-input
                              :model-value="section.video?.url"
                              :placeholder="translate('YouTube, Vimeo or direct MP4 URL')"
                              @input="value => updateVideoUrl(index, value)"
                              :disabled="isPremiumLocked"
                          />

                          <div class="fct-section-video-actions">
                            <MediaButton
                                icon="Video"
                                :title="section.video?.url ? translate('Replace video') : translate('Upload video')"
                                :attachments="getVideoAttachments(section)"
                                :library-type="'video'"
                                :multiple="false"
                              @onMediaSelected="selected => onVideoSelected(index, selected)"
                              :class="{'fct-pointer-none': isPremiumLocked}"
                            />
                            <el-button
                                v-if="hasVideoUrl(section)"
                                text
                                type="danger"
                                @click="clearVideo(index)"
                                :disabled="isPremiumLocked"
                            >
                              {{ translate('Remove') }}
                            </el-button>
                        </div>
                      </div>

                      <div v-if="hasVideoUrl(section)" class="fct-section-attachment">
                        <div class="fct-section-attachment__info">
                          <a :href="section.video.url" target="_blank" rel="noopener" class="fct-section-attachment__title">{{ getVideoLabel(section) }}</a>
                          <span class="fct-section-media-meta">{{ translate('Video attachment') }}</span>
                        </div>
                      </div>
                    </div>
                  </el-form-item>
                </div>
              </el-form>
            </div>
          </el-collapse-item>
        </el-collapse>

          <div class="fct-custom-section-footer">
            <el-button
                type="primary"
                plain
                :disabled="isPremiumLocked || hasReachedLimit"
                @click="addSection"
            >
              {{ translate('Add section') }}
            </el-button>
            <p class="text-muted" v-if="hasReachedLimit">{{ translate('You can add up to four sections.') }}</p>
          </div>

          <div v-if="isPremiumLocked" class="fct-premium-overlay" tabindex="0">
            <div class="fct-premium-overlay__content">
              <p class="fct-premium-overlay__title">{{ translate('This is a premium feature') }}</p>
              <p class="fct-premium-overlay__text">{{ translate('Upgrade to unlock Featured Video and Custom Sections.') }}</p>
              <el-button type="primary" tag="a" href="https://webmakerr.com/item/webmakerr-pro-plugin" target="_blank">
                {{ translate('Upgrade Now') }}
              </el-button>
              <p class="fct-premium-overlay__subtext">{{ translate('Available in Pro plan') }}</p>
            </div>
          </div>
        </div>
      </Card.Body>
    </Card.Container>
  </div>
</template>

<style lang="scss" scoped>
.fct-product-custom-section-wrap {
  @apply mt-4;
}

.fct-custom-sections {
  @apply flex flex-col gap-3;
}

.fct-custom-section {
  @apply bg-white border border-solid border-gray-200 rounded overflow-hidden;

  :deep(.el-collapse-item__header) {
    @apply px-4 py-3 items-center;
  }

  :deep(.el-collapse-item__wrap) {
    @apply border-t border-solid border-gray-200;
  }

  :deep(.el-collapse-item__content) {
    @apply p-0;
  }
}

.fct-custom-section__header {
  @apply flex items-center justify-between w-full;
}

.fct-custom-section__title {
  @apply text-base font-semibold text-gray-800 m-0;
}

.fct-custom-section__body {
  @apply p-4;
}

.fct-section-media-picker {
  @apply flex flex-col gap-3;
}

.fct-section-media-actions {
  @apply flex items-center gap-3 flex-wrap;
}

.fct-section-video-actions {
  @apply flex items-center gap-2;
}

.fct-section-attachment {
  @apply flex items-center gap-3 flex-wrap border border-solid border-gray-200 rounded px-3 py-2 bg-gray-50;
}

.fct-section-attachment__thumb {
  @apply w-16 h-16 rounded overflow-hidden bg-white border border-solid border-gray-200 flex items-center justify-center;

  img {
    @apply w-full h-full object-cover;
  }
}

.fct-section-attachment__info {
  @apply flex flex-col gap-1 text-sm text-gray-700;
}

.fct-section-attachment__title {
  @apply font-medium text-blue-600 break-all hover:underline;
}

.fct-section-attachment__actions {
  @apply ml-auto;
}

.fct-section-media-meta {
  @apply text-xs text-gray-500;
}

.fct-custom-section-footer {
  @apply pt-2 flex items-center gap-3;

  .text-muted {
    @apply text-sm text-gray-500 m-0;
  }
}

.fct-premium-gated {
  @apply relative;
}

.fct-premium-overlay {
  @apply absolute inset-0 bg-black bg-opacity-60 flex items-center justify-center text-center px-4 rounded-lg;
  opacity: 0;
  pointer-events: auto;
  transition: opacity 0.2s ease-in-out;
  z-index: 1;
}

.fct-premium-overlay__content {
  @apply flex flex-col gap-2 items-center justify-center text-white;
}

.fct-premium-overlay__title {
  @apply text-lg font-semibold m-0;
}

.fct-premium-overlay__text,
.fct-premium-overlay__subtext {
  @apply text-sm m-0;
}

.fct-premium-gated.is-locked .fct-premium-overlay {
  opacity: 0;
}

.fct-premium-gated.is-locked:hover .fct-premium-overlay,
.fct-premium-gated.is-locked:focus-within .fct-premium-overlay,
.fct-premium-gated.is-locked:active .fct-premium-overlay {
  opacity: 1;
}

.fct-premium-gated.is-locked .fct-premium-overlay {
  @apply pointer-events-auto;
}

.fct-pointer-none {
  pointer-events: none;
}
</style>
