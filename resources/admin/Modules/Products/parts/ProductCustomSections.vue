<script setup>
import * as Card from '@/Bits/Components/Card/Card.js';
import MediaButton from "@/Bits/Components/Buttons/MediaButton.vue";
import translate from "@/utils/translator/Translator";
import {computed, ref, watch} from "vue";

const props = defineProps({
  product: Object,
  productEditModel: Object
})

const maxSections = 4;

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

watch(() => props.product?.detail?.other_info?.custom_sections, (value) => {
  sections.value = normalizeSections(value);
}, {immediate: true});

const hasReachedLimit = computed(() => sections.value.length >= maxSections);

const persistSections = () => {
  props.productEditModel.onChangeInputField('custom_sections', sections.value);
};

const updateSectionField = (index, key, value) => {
  const updatedSections = [...sections.value];
  const section = normalizeSection(updatedSections[index]);
  section[key] = value;
  updatedSections.splice(index, 1, section);
  sections.value = updatedSections;
  persistSections();
};

const addSection = () => {
  if (hasReachedLimit.value) {
    return;
  }

  sections.value = [...sections.value, getDefaultSection()];
  persistSections();
};

const removeSection = (index) => {
  const updated = [...sections.value];
  updated.splice(index, 1);
  sections.value = normalizeSections(updated);
  persistSections();
};

const onImageSelected = (index, selected) => {
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
  updateSectionField(index, 'image', null);
};

const onVideoSelected = (index, selected) => {
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
  const url = value ? value.trim() : '';
  const current = normalizeVideo(sections.value[index]?.video);

  current.url = url;
  current.type = url ? detectVideoType(url) : '';

  updateSectionField(index, 'media_type', 'video');
  updateSectionField(index, 'video', current);
};

const clearVideo = (index) => {
  updateSectionField(index, 'video', getDefaultVideo());
};

const getVideoAttachments = (section) => {
  return section.video?.id && section.video?.url ? [{
    id: section.video.id,
    title: section.video.title ?? '',
    url: section.video.url
  }] : [];
};

const hasVideoUrl = (section) => !!section?.video?.url;

const isVideoFile = (section) => {
  const url = section?.video?.url;
  if (!url) {
    return false;
  }

  const type = section?.video?.type;
  if (type === 'file') {
    return true;
  }

  return /\.(mp4|mov|webm|ogg)(\?.*)?$/i.test(url);
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
        <div class="fct-custom-sections">
          <div
              v-for="(section, index) in sections"
              :key="`section-${index}`"
              class="fct-custom-section"
          >
            <div class="fct-custom-section__header">
              <p class="fct-custom-section__title">
                {{ translate('Section') }} {{ index + 1 }}
              </p>
              <el-button
                  v-if="sections.length > 1"
                  text
                  type="danger"
                  size="small"
                  @click="removeSection(index)"
              >
                {{ translate('Remove') }}
              </el-button>
            </div>

            <el-form label-position="top" require-asterisk-position="right">
              <div class="fct-admin-input-wrapper">
                <el-form-item :label="translate('Heading')">
                  <el-input
                      :placeholder="translate('Add heading')"
                      :model-value="section.title"
                      @input="value => updateSectionField(index, 'title', value)"
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
                  />
                </el-form-item>
              </div>

              <div class="fct-admin-input-wrapper">
                <el-form-item :label="translate('Media type')">
                  <el-radio-group
                      :model-value="section.media_type"
                      @change="value => updateSectionField(index, 'media_type', value)"
                  >
                    <el-radio-button label="image">{{ translate('Image') }}</el-radio-button>
                    <el-radio-button label="video">{{ translate('Video') }}</el-radio-button>
                  </el-radio-group>
                </el-form-item>
              </div>

              <div v-if="section.media_type === 'image'" class="fct-admin-input-wrapper">
                <el-form-item :label="translate('Section image')">
                  <div class="fct-section-media-picker">
                    <div v-if="section.image?.url" class="fct-section-media-preview">
                      <img :src="section.image.url" :alt="section.image.title || section.title || translate('Section image')">
                    </div>

                    <div class="fct-section-media-actions">
                      <MediaButton
                          icon="Picture"
                          :title="section.image ? translate('Change image') : translate('Select image')"
                          :attachments="section.image ? [section.image] : []"
                          :multiple="false"
                          @onMediaSelected="selected => onImageSelected(index, selected)"
                      />

                      <el-button
                          v-if="section.image"
                          text
                          type="danger"
                          @click="clearImage(index)"
                      >
                        {{ translate('Remove') }}
                      </el-button>
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
                      />

                      <div class="fct-section-video-actions">
                        <MediaButton
                            icon="Video"
                            :title="section.video?.url ? translate('Replace video') : translate('Upload video')"
                            :attachments="getVideoAttachments(section)"
                            :library-type="'video'"
                            :multiple="false"
                            @onMediaSelected="selected => onVideoSelected(index, selected)"
                        />
                        <el-button
                            v-if="hasVideoUrl(section)"
                            text
                            type="danger"
                            @click="clearVideo(index)"
                        >
                          {{ translate('Remove') }}
                        </el-button>
                      </div>
                    </div>

                    <div v-if="hasVideoUrl(section)" class="fct-section-video-preview">
                      <video
                          v-if="isVideoFile(section)"
                          :src="section.video.url"
                          controls
                          playsinline
                      ></video>
                      <iframe
                          v-else
                          :src="section.video.url"
                          allowfullscreen
                          loading="lazy"
                      ></iframe>
                    </div>
                  </div>
                </el-form-item>
              </div>
            </el-form>
          </div>
        </div>

        <div class="fct-custom-section-footer">
          <el-button
              type="primary"
              plain
              :disabled="hasReachedLimit"
              @click="addSection"
          >
            {{ translate('Add section') }}
          </el-button>
          <p class="text-muted" v-if="hasReachedLimit">{{ translate('You can add up to four sections.') }}</p>
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
  @apply flex flex-col gap-4;
}

.fct-custom-section {
  @apply border border-solid border-gray-200 rounded p-4 bg-white;

  .fct-custom-section__header {
    @apply flex items-center justify-between mb-3;
  }

  .fct-custom-section__title {
    @apply text-base font-semibold text-gray-800 m-0;
  }
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

.fct-section-media-preview,
.fct-section-video-preview {
  @apply rounded overflow-hidden border border-solid border-gray-200 bg-gray-50;

  img, video, iframe {
    @apply w-full h-full object-cover;
    min-height: 200px;
  }

  iframe {
    border: 0;
  }
}

.fct-custom-section-footer {
  @apply pt-2 flex items-center gap-3;

  .text-muted {
    @apply text-sm text-gray-500 m-0;
  }
}
</style>
