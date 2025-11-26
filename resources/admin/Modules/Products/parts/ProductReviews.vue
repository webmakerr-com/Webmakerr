<script setup>
import * as Card from '@/Bits/Components/Card/Card.js';
import translate from "@/utils/translator/Translator";
import countries from "@/Modules/Customers/countries.json";
import {computed, ref, watch} from "vue";

const props = defineProps({
  product: Object,
  productEditModel: Object
});

const maxReviews = 15;

const toFlagEmoji = (code) => {
  if (!code || typeof code !== 'string' || code.length < 2) {
    return '';
  }

  const upper = code.slice(0, 2).toUpperCase();
  const codePoints = [...upper].map(char => 127397 + char.charCodeAt(0));
  return String.fromCodePoint(...codePoints);
};

const getDefaultReview = () => ({
  name: '',
  text: '',
  rating: 0,
  country: '',
  date: ''
});

const clampRating = (value) => {
  const rating = Number.isFinite(value) ? value : parseFloat(value || 0);
  if (Number.isNaN(rating)) {
    return 0;
  }

  return Math.min(Math.max(parseFloat(rating.toFixed(1)), 0), 5);
};

const normalizeReview = (review) => {
  if (!review || typeof review !== 'object') {
    return getDefaultReview();
  }

  return {
    name: review.name?.toString().trim() || '',
    text: review.text?.toString() || '',
    rating: clampRating(review.rating ?? 0),
    country: review.country?.toString().slice(0, 3).toUpperCase() || '',
    date: review.date || ''
  };
};

const normalizeReviews = (list) => {
  if (!Array.isArray(list)) {
    return [getDefaultReview()];
  }

  const normalized = list
      .map(normalizeReview)
      .filter(review => review.name || review.text || review.rating > 0)
      .slice(0, maxReviews);

  return normalized.length ? normalized : [getDefaultReview()];
};

const reviews = ref(normalizeReviews(props.product?.detail?.other_info?.reviews));

watch(() => props.product?.detail?.other_info?.reviews, (value) => {
  reviews.value = normalizeReviews(value);
}, {immediate: true});

const hasReachedLimit = computed(() => reviews.value.length >= maxReviews);

const persistReviews = () => {
  props.productEditModel.onChangeInputField('reviews', reviews.value);
};

const addReview = () => {
  if (hasReachedLimit.value) {
    return;
  }

  reviews.value = [...reviews.value, getDefaultReview()];
  persistReviews();
};

const removeReview = (index) => {
  const updated = [...reviews.value];
  updated.splice(index, 1);
  reviews.value = normalizeReviews(updated);
  persistReviews();
};

const updateReviewField = (index, key, value) => {
  const updated = [...reviews.value];
  updated[index] = normalizeReview({
    ...updated[index],
    [key]: value
  });
  reviews.value = updated;
  persistReviews();
};

const countryOptions = computed(() => countries.map(country => ({
  value: country.code2,
  label: `${toFlagEmoji(country.code2)} ${country.name}`.trim()
})));
</script>

<template>
  <div class="fct-product-reviews-wrap">
    <Card.Container>
      <Card.Header
          :title="translate('Reviews')"
          :desc="translate('Add up to fifteen reviews with ratings, flags, and dates. Avatars are generated from initials automatically.')"
          border_bottom
      />
      <Card.Body>
        <div class="fct-product-reviews" v-if="reviews.length">
          <div
              class="fct-review"
              v-for="(review, index) in reviews"
              :key="`review-${index}`"
          >
            <div class="fct-review__header">
              <p class="fct-review__title">{{ translate('Review') }} {{ index + 1 }}</p>
              <el-button
                  v-if="reviews.length > 1"
                  text
                  type="danger"
                  size="small"
                  @click="removeReview(index)"
              >
                {{ translate('Remove') }}
              </el-button>
            </div>

            <el-form label-position="top" require-asterisk-position="right" class="fct-review__form">
              <div class="fct-review__grid">
                <div class="fct-admin-input-wrapper">
                  <el-form-item :label="translate('Reviewer name')">
                    <el-input
                        :model-value="review.name"
                        :placeholder="translate('Add reviewer name')"
                        @input="value => updateReviewField(index, 'name', value)"
                    />
                  </el-form-item>
                </div>

                <div class="fct-admin-input-wrapper">
                  <el-form-item :label="translate('Rating (0 - 5)')">
                    <el-input-number
                        :model-value="review.rating"
                        :min="0"
                        :max="5"
                        :step="0.1"
                        :precision="1"
                        @update:model-value="value => updateReviewField(index, 'rating', value)"
                    />
                  </el-form-item>
                </div>

                <div class="fct-admin-input-wrapper">
                  <el-form-item :label="translate('Country')">
                    <el-select
                        filterable
                        clearable
                        :model-value="review.country"
                        :placeholder="translate('Choose a country')"
                        @change="value => updateReviewField(index, 'country', value)"
                    >
                      <el-option
                          v-for="country in countryOptions"
                          :key="country.value"
                          :label="country.label"
                          :value="country.value"
                      />
                    </el-select>
                  </el-form-item>
                </div>

                <div class="fct-admin-input-wrapper">
                  <el-form-item :label="translate('Date')">
                    <el-date-picker
                        type="date"
                        format="YYYY-MM-DD"
                        value-format="YYYY-MM-DD"
                        :model-value="review.date"
                        :placeholder="translate('YYYY-MM-DD')"
                        @update:model-value="value => updateReviewField(index, 'date', value)"
                    />
                  </el-form-item>
                </div>
              </div>

              <div class="fct-admin-input-wrapper">
                <el-form-item :label="translate('Review text')">
                  <el-input
                      type="textarea"
                      :rows="3"
                      :model-value="review.text"
                      :placeholder="translate('Add the review text')"
                      @input="value => updateReviewField(index, 'text', value)"
                  />
                </el-form-item>
              </div>
            </el-form>
          </div>
        </div>

        <div class="fct-review-footer">
          <el-button
              type="primary"
              plain
              :disabled="hasReachedLimit"
              @click="addReview"
          >
            {{ translate('Add review') }}
          </el-button>
          <p class="text-muted" v-if="hasReachedLimit">{{ translate('You can add up to fifteen reviews.') }}</p>
        </div>
      </Card.Body>
    </Card.Container>
  </div>
</template>

<style scoped lang="scss">
.fct-product-reviews-wrap {
  @apply mt-4;
}

.fct-product-reviews {
  @apply flex flex-col gap-4;
}

.fct-review {
  @apply bg-white border border-solid border-gray-200 rounded p-4;
}

.fct-review__header {
  @apply flex items-center justify-between mb-3;
}

.fct-review__title {
  @apply text-base font-semibold text-gray-800 m-0;
}

.fct-review__form {
  @apply flex flex-col gap-3;
}

.fct-review__grid {
  @apply grid grid-cols-1 gap-3 md:grid-cols-2;
}

.fct-admin-input-wrapper {
  @apply w-full;
}

.fct-review-footer {
  @apply pt-2 flex items-center gap-3;

  .text-muted {
    @apply text-sm text-gray-500 m-0;
  }
}
</style>
