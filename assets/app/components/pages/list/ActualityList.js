// modules
import React, { useEffect, useState } from 'react';
import { useDispatch, useSelector } from 'react-redux';
import { Link } from 'react-router-dom';
import { Button, Form, Loader } from 'semantic-ui-react';
import useDarkMode from '../../../hooks/useDarkMode';
import useFetch from '../../../hooks/useFetch';
import usePager from '../../../hooks/usePager';
import ActualityTeaser from '../../elements/ActualityTeaser';
import PageWrapper from '../../PageWrapper';
import PaginationPrettier from '../../PaginationPrettier';
import { setActualityTags } from '../../../reducers/actuality_tags';
import useActions from '../../../hooks/useActions';
import { objectToGETparams } from '../../../functions';
import DropdownMultipleSelectField from '../../fields/DropdownMultipleSelectField';
import useStoreQuery from '../../../hooks/useStoreQuery';
import SectionAds from '../../sections/SectionAds';

const ActualityList = () => {
	const dispatch = useDispatch();
	const { user, actuality_tags } = useSelector(state => state);
	const [darkMode] = useDarkMode();
	const [result, load, loading] = useFetch();
	const [actualities, setActualities] = useState([]);
	const [query, setQuery, updateQuery, setQueryParam] = useStoreQuery(true);
	const [table, page, nbPages, handlePage] = usePager(24, actualities);
	const [resultTags, loadTags, loadingTags] = useFetch();
	const [setTags] = useActions(dispatch, [setActualityTags]);
	const [checkedTags, setCheckedTags] = useState(
		query.tags ? (Array.isArray(query.tags) ? query.tags : query.tags.split(',')) : []
	);

	useEffect(() => {
		if (!actualities.length || Object.keys(query).length > 1) {
			handleLoad();
		}
	}, [query.tags]);

	useEffect(() => {
		if (result && result.success) {
			setActualities(result.actualities);
		}
	}, [result]);

	useEffect(() => {
		if (!actuality_tags.length) {
			loadTags({ url: 'actuality_tags' });
		}
	}, []);

	useEffect(() => {
		if (resultTags && resultTags.success) {
			setTags(resultTags.tags);
		}
	}, [resultTags]);

	const handleLoad = () => load({ url: 'actualities' + objectToGETparams(query) });

	const handleSubmitFilters = e => {
		e.preventDefault();
		setQueryParam('tags', checkedTags);
	};

	return (
		<PageWrapper
			title="Toutes les actualités"
			className="actuality-list"
		>
			<SectionAds />
			{user.is_modo && (
				<Button
					as={Link}
					to="/entity/actualities/create"
					color="blue"
					content="Ajouter une actualité"
					icon="plus"
					className="mb-4"
				/>
			)}
			<Form onSubmit={handleSubmitFilters} className="mb-4">
				{loadingTags ? (
					<Loader active inline="centered" />
				) : (
					<DropdownMultipleSelectField
						label="Catégories"
						name="tags"
						className="flex-grow-1"
						options={actuality_tags}
						value={checkedTags}
						onChange={(e, { value }) => setCheckedTags(value)}
					/>
				)}
				<Button color="orange" content="Valider le filtre" type="submit" />
			</Form>
			{nbPages > 1 && (
				<PaginationPrettier
					activePage={page}
					totalPages={nbPages}
					onPageChange={handlePage}
				/>
			)}
			<div id="pagination-scroll-ref">
				{loading ? (
					<Loader inline="centered" active />
				) : table.length > 0 ? (
					<div className="row">
						{table.map(actuality => (
							<div
								key={actuality.id}
								className="col-12 col-lg-4 d-flex flex-column"
							>
								<ActualityTeaser
									actuality={actuality}
									darkMode={darkMode}
								/>
							</div>
						))}
					</div>
				) : (
					<p>Aucune actualité disponible.</p>
				)}
			</div>
			{nbPages > 1 && (
				<PaginationPrettier
					activePage={page}
					totalPages={nbPages}
					onPageChange={handlePage}
				/>
			)}
			<SectionAds />
		</PageWrapper>
	);
};

export default ActualityList;
